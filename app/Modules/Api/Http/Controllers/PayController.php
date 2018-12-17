<?php

namespace App\Modules\Api\Http\Controllers;

use App\Http\Controllers\ApiBaseController;
use App\Modules\Finance\Model\CashoutModel;
use App\Modules\Finance\Model\FinancialModel;
use App\Modules\Manage\Model\ConfigModel;
use App\Modules\Task\Model\ServiceModel;
use App\Modules\Order\Model\OrderModel;
use App\Modules\Task\Model\TaskModel;
use App\Modules\Task\Model\TaskServiceModel;
use App\Modules\Task\Model\TaskTypeModel;
use App\Modules\User\Model\AlipayAuthModel;
use App\Modules\User\Model\BankAuthModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Omnipay;
use DB;
use Validator;

class PayController extends ApiBaseController
{
    protected $uid;

    public function __construct(Request $request)
    {
        $tokenInfo = Crypt::decrypt($request->get('token'));
        $this->uid = $tokenInfo['uid'];
    }

    
    public function taskDepositByBalance(Request $request)
    {

        $data = $request->all();
        $validator = Validator::make($data, [
            'task_id' => 'required',
            'pay_type' => 'required',
        ], [
            'task_id.required' => '请选择要托管的任务',
            'pay_type.required' => '请选择支付方式',
        ]);
        $error = $validator->errors()->all();
        if (count($error)) {
            return $this->formateResponse(2010, $error[0]);
        }
        $task_id = $data['task_id'];
        
        $task = TaskModel::where('id', $task_id)->first();
        if ($task->uid != $this->uid
) {
            return $this->formateResponse(2011, '非法操作');
        }

        
        $taskType = TaskTypeModel::getTaskTypeAliasById($task['type_id']);
        switch($taskType){
            case 'xuanshang' :
                if ($task->status >= 2) {
                    return $this->formateResponse(1071, '非法操作');
                }
                
                $taskModel = new TaskModel();
                $money = $taskModel->taskMoney($task_id);
                break;
            case 'zhaobiao':
                if($task->status == 1){
                    
                    $service = TaskServiceModel::select('task_service.service_id')
                        ->where('task_id', '=', $task_id)->get()->toArray();
                    $service = array_flatten($service);
                    $money = ServiceModel::serviceMoney($service);
                }elseif($task->status == 5){
                    $money = $task['bounty'];
                }else{
                    $money = 0;
                }
                break;
            default:
                
                $taskModel = new TaskModel();
                $money = $taskModel->taskMoney($task_id);
        }
        
        $user = UserModel::where('id', $this->uid)->first();
        $userDetail = UserDetailModel::where('uid', $this->uid)->first();
        $balance = (float)$userDetail->balance;

        if ($balance >= $money && $data['pay_type'] == 0) {
            
            $order = $this->createTaskOrder($task_id);
            if (!$order) {
                return $this->formateResponse(2012, '创建订单失败');
            }
            $alternate_password = UserModel::encryptPassword($data['password'], $user->salt);
            if ($alternate_password != $user->alternate_password) {
                return $this->formateResponse(2013, '支付密码不正确');
            }
            switch($taskType){
                case 'xuanshang' :
                    $result = TaskModel::bounty($money, $task_id, $this->uid, $order->code);
                    break;
                case 'zhaobiao':
                    if($task->status == 1){
                        $waitHandle = OrderModel::where('task_id', $task_id)->where('status',0)->where('code','like','ts'.'%')->first();
                        if (!empty($waitHandle)){
                            $result = TaskModel::buyServiceTaskBid($waitHandle->cash, $waitHandle->task_id, $waitHandle['uid'], $waitHandle->code);
                        }else{
                            $result = true;
                        }
                    }elseif($task->status == 5){
                        $result = TaskModel::bidBounty($money, $task_id, $this->uid, $order->code);
                    }
                    break;
            }

            if (isset($result) && $result) {
                return $this->formateResponse(1000, 'success');
            } else {
                return $this->formateResponse(2014, '付款失败');
            }
        } else {
            return $this->formateResponse(2015, '余额支付失败');
        }
    }

    
    private function createTaskOrder($taskId)
    {
        
        $task = TaskModel::where('id', $taskId)->first();
        if(!$task){
            return false;
        }
        if ($task->uid != $this->uid) {
            return false;
        }
        
        $taskType = TaskTypeModel::getTaskTypeAliasById($task['type_id']);

        switch($taskType){
            case 'xuanshang':
                
                $orderInfo = OrderModel::where('task_id', $taskId)->where('status',0)->first();
                break;
            case 'zhaobiao':
                if($task->status == 1){
                    
                    $orderInfo = OrderModel::where('task_id', $taskId)->where('status',0)->where('code','like','ts'.'%')->first();
                }else{
                    
                    $orderInfo = OrderModel::where('task_id', $taskId)->where('status',0)->first();
                }
                break;
            default:
                
                $orderInfo = OrderModel::where('task_id', $taskId)->where('status',0)->first();
        }

        if ($orderInfo) {
            $order = $orderInfo;
        } else {
            switch($taskType){
                case 'xuanshang':
                    if ($task->status >= 2) {
                        return false;
                    }
                    
                    $taskModel = new TaskModel();
                    $money = $taskModel->taskMoney($taskId);
                    
                    $order = OrderModel::bountyOrder($this->uid, $money, $taskId);
                    break;
                case 'zhaobiao':
                    if($task->status == 1){
                        
                        $service = TaskServiceModel::select('task_service.service_id')
                            ->where('task_id', '=', $taskId)->get()->toArray();
                        $service = array_flatten($service);
                        $money = ServiceModel::serviceMoney($service);
                        if($money > 0){
                            
                            $order = OrderModel::buyServicebyTaskBid($this->uid, $money, $taskId);
                        }else{
                            return false;
                        }

                    }elseif($task->status == 5){
                        $money = $task['bounty'];
                        
                        $order = OrderModel::bountyOrderByTaskBid($this->uid, $money, $taskId);
                    }
                    break;
            }
        }
        if(isset($order)){
            return $order;
        }else{
            return false;
        }

    }

    
    public function createOrderInfo(Request $request)
    {
        $data = $request->all();

        $task_id = $request->get('task_id');
        $order = $this->createTaskOrder($task_id);
        if ($order) {
            return $this->formateResponse(1000, 'success', $order);
        } else {
            return $this->formateResponse(2022, '订单创建失败');
        }
    }

    
    public function checkThirdConfig(Request $request)
    {

        $pay_type = $request->get('pay_type');
        $configInfo = $pay_type_name = '';
        $status = 1;
        switch ($pay_type) {
            case 1:
                $configInfo = ConfigModel::getPayConfig('alipay');
                $pay_type_name = '支付宝';
                break;
            case 2:
                $configInfo = ConfigModel::getPayConfig('wechatpay');
                $pay_type_name = '微信支付';
                break;
            case 3:

                $configInfo = null;
                $pay_type_name = '银联支付';
                break;
        }
        
        if (is_array($configInfo)) {
            foreach ($configInfo as $con) {
                if (empty($con)) {
                    $status = 0;
                }
            }
        }
        if (!$configInfo) {
            $status = 0;
        }

        if ($status) {
            return $this->formateResponse(1000, 'success', $configInfo);
        } else {
            return $this->formateResponse(2021, $pay_type_name . '配置信息不全');
        }
    }

    
    public function balance()
    {
        $userDetail = UserDetailModel::where('uid', $this->uid)->first();
        $data = array(
            'balance' => $userDetail->balance
        );
        return $this->formateResponse(1000, 'success', $data);
    }

    
    public function financeList(Request $request)
    {
        $data = $request->all();
        $data['timeStatus'] = isset($data['timeStatus']) ? $data['timeStatus'] : 0;
        $finance = FinancialModel::where('uid', $this->uid);
        if (isset($data['timeStatus'])) {
            $sql = 'date_format(created_at,"%Y-%m")=date_format(date_sub(now(),interval ' . $data['timeStatus'] . ' month),"%Y-%m")';
            if ($data['timeStatus']) {
                $finance = $finance->whereRaw($sql);
            } else {
                $finance = $finance->whereRaw('date_format(created_at,"%Y-%m")=date_format(now(),"%Y-%m")');
            }
        }
        $finance = $finance->orderBy('created_at','desc')->paginate(5)->toArray();
        $userInfo = UserDetailModel::where('uid',$this->uid)->select('balance')->first();
        $financeInfo = [
            'balance' => $userInfo->balance,
            'finance' => $finance['data']
        ];
        return $this->formateResponse(1000,'success',$financeInfo);
    }

    
    public function cashOut(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'cash' => 'required|numeric',
            'cashout_type' => 'required',
            'cashout_account' => 'required',
            'alternate_password' => 'required',
        ], [
            'cash.required' => '请输入提现金额',
            'cash.numeric' => '请输入正确的金额格式',
            'cashout_type.required' => '请选择提现方式',
            'cashout_account.required' => '请输入提现账户',
            'alternate_password.required' => '请输入支付密码',
        ]);
        $error = $validator->errors()->all();
        if (count($error)) {
            return $this->formateResponse(1070,$error[0]);
        }

        $userDetail = UserDetailModel::where('uid', $this->uid)->first();
        $user = UserModel::where('id', $this->uid)->first();
        $balance = $userDetail->balance;
        
        $cashConfig = ConfigModel::getConfigByAlias('cash');
        $rule = json_decode($cashConfig->rule, true);

        
        $now = strtotime(date('Y-m-d'));
        $start = date('Y-m-d H:i:s', $now);
        $end = date('Y-m-d H:i:s', $now + 24 * 3600);

        
        $cashOutSum = CashoutModel::where('uid', $this->uid)->whereBetween('created_at', [$start, $end])->sum('cash');
        $message = '';
        if ($data['cash'] > $balance) {
            return $this->formateResponse(1071,'提现金额不得大于账户余额');
        }
        if ($rule['withdraw_min'] && $data['cash'] < $rule['withdraw_min']) {
            return $this->formateResponse(1072,'单笔提现金额不得小于' . $rule['withdraw_min'] . '元');
        }
        if ($rule['withdraw_max'] && $cashOutSum > $rule['withdraw_max']) {
            return $this->formateResponse(1073,'当日提现金额不得大于' . $rule['withdraw_max'] . '元');
        }

        $alternate_password = UserModel::encryptPassword($data['alternate_password'], $user->salt);
        if ($alternate_password === $user->alternate_password) {
            $fees = FinancialModel::getFees($data['cash']);
            $info = array(
                'uid' => $this->uid,
                'cash' => $data['cash'],
                'fees' => $fees,
                'real_cash' => $data['cash'] - $fees,
                'cashout_type' => $data['cashout_type'],
                'cashout_account' => $data['cashout_account'],
            );

            $status = $this->addCashOut($info);
            if ($status) {
                return $this->formateResponse(1000, 'success');
            } else {
                return $this->formateResponse(1075, '提现失败');
            }
        } else {
            return $this->formateResponse(1074, '支付密码不正确');
        }
    }

    
    public function bankAccount()
    {
        $bankCard = BankAuthModel::where('uid', $this->uid)->where('status', 2)->get();
        if (count($bankCard)) {

            return $this->formateResponse(1000, 'success', $bankCard);
        } else {
            return $this->formateResponse(2017, '暂无已认证的银行卡信息');
        }
    }

    
    public function alipayAccount()
    {
        $alipay = AlipayAuthModel::where('uid', $this->uid)->where('status', 2)->get();
        if (count($alipay)) {
            return $this->formateResponse(1000, 'success', $alipay);
        } else {
            return $this->formateResponse(2018, '暂无已认证的支付宝信息');
        }
    }

    
    static function addCashOut($data)
    {
        $status = DB::transaction(function () use ($data) {
            CashoutModel::create($data);
            $finance = array(
                'action' => 4,
                'pay_account' => $data['cashout_account'],
                'cash' => $data['cash'],
                'uid' => $data['uid'],
                'created_at' => date('Y-m-d H:i:d', time()),
            );
            if ($data['cashout_type'] == 1) {
                $finance['pay_type'] = 2;
            } elseif ($data['cashout_type'] == 2) {
                $finance['pay_type'] = 4;
            }
            FinancialModel::create($finance);
            UserDetailModel::where('uid', $data['uid'])->decrement('balance', $data['cash']);
        });
        return is_null($status) ? true : false;
    }

    
    public function alipayNotify()
    {
        if (app('alipay.mobile')->verify()) {
            $data = [
                'pay_account' => Input::get('buy_email'),
                'code' => Input::get('out_trade_no'),
                'pay_code' => Input::get('trade_no'),
                'money' => Input::get('total_fee')
            ];

            
            switch (Input::get('trade_status')) {
                case 'TRADE_SUCCESS':
                case 'TRADE_FINISHED':
                    $orderInfo = OrderModel::where('code', $data['code'])->first();
                    if (!empty($orderInfo)) {
                        if ($orderInfo->task_id) {
                            $uid = $orderInfo->uid;
                            $money = $data['money'];
                            $task_id = $orderInfo->task_id;
                            $code = $data['code'];
                            $result = DB::transaction(function () use ($money, $task_id, $uid, $code) {
                                
                                $data = self::where('id', $this->task_id)->update(['bounty_status' => 1,'status' => 2]);
                                
                                $financial = [
                                    'action' => 1,
                                    'pay_type' => 2,
                                    'cash' => $money,
                                    'uid' => $uid,
                                    'created_at' => date('Y-m-d H:i:s', time())
                                ];
                                FinancialModel::create($financial);
                                
                                OrderModel::where('code', $code)->update(['status' => 1]);

                                
                                
                                $bounty_limit = \CommonClass::getConfig('task_bounty_limit');
                                if ($bounty_limit < $money) {
                                    self::where('id', '=', $task_id)->update(['status' => 3]);
                                } else {
                                    self::where('id', '=', $task_id)->update(['status' => 2]);
                                }
                                
                                UserDetailModel::where('uid', $uid)->increment('publish_task_num', 1);
                                return true;
                            });
                        } else {
                            $result = UserDetailModel::recharge($orderInfo->uid, 2, $data);
                        }


                        if (!$result) {
                            return $this->formateResponse(2022, '支付失败');
                        }

                        return $this->formateResponse(1000, 'success');
                    }
                    return $this->formateResponse(2023, '订单信息错误');
                    break;
            }

            return $this->formateResponse(2023, '支付失败');
        }
    }

    
    public function wechatpayNotify()
    {
        Log::info('微信支付回调');
        $gateway = Omnipay::gateway('WechatPay');

        $response = $gateway->completePurchase([
            'request_params' => file_get_contents('php://input')
        ])->send();

        if ($response->isPaid()) {
            
            $result = $response->getData();
            $data = [
                'pay_account' => $result['openid'],
                'code' => $result['out_trade_no'],
                'pay_code' => $result['transaction_id'],
                'money' => $result['total_fee']
            ];
            $orderInfo = OrderModel::where('code', $data['code'])->first();
            if (!empty($orderInfo)) {
                if ($orderInfo->task_id) {
                    $uid = $orderInfo->uid;
                    $money = $data['money'];
                    $task_id = $orderInfo->task_id;
                    $code = $data['code'];
                    $result = DB::transaction(function () use ($money, $task_id, $uid, $code) {
                        
                        $data = self::where('id', $this->task_id)->update(['bounty_status' => 1,'status' => 2]);
                        
                        $financial = [
                            'action' => 1,
                            'pay_type' => 3,
                            'cash' => $money,
                            'uid' => $uid,
                            'created_at' => date('Y-m-d H:i:s', time())
                        ];
                        FinancialModel::create($financial);
                        
                        OrderModel::where('code', $code)->update(['status' => 1]);

                        
                        
                        $bounty_limit = \CommonClass::getConfig('task_bounty_limit');
                        if ($bounty_limit < $money) {
                            self::where('id', '=', $task_id)->update(['status' => 3]);
                        } else {
                            self::where('id', '=', $task_id)->update(['status' => 2]);
                        }
                        
                        UserDetailModel::where('uid', $uid)->increment('publish_task_num', 1);
                        return true;
                    });
                } else {
                    $result = UserDetailModel::recharge($orderInfo->uid, 2, $data);
                }


                if (!$result) {
                    return $this->formateResponse(2022, '支付失败');
                }

                return $this->formateResponse(1000, 'success');
            }

        } else {
            
        }
    }


    
    public function postCash(Request $request)
    {
        if ($request->get('task_id')) {
            $task_id = $request->get('task_id');
            
            $order = $this->createTaskOrder($task_id);

        } else {
            $data = array(
                'code' => OrderModel::randomCode($this->uid),
                'title' => $request->get('title'),
                'cash' => $request->get('cash'),
                'uid' => $this->uid,
                'created_at' => date('Y-m-d H:i:s', time()),
                'note' => $request->get('note'),
                'task_id' => $request->get('task_id')
            );
            $order = OrderModel::create($data);
        }

        if ($order && $order->cash > 0) {
            $payType = $request->get('pay_type');
            switch ($payType) {
                case 'alipay':
                    $config = ConfigModel::getConfigByAlias('app_alipay');
                    $info = [];
                    if($config && !empty($config['rule'])){
                        $info = json_decode($config['rule'],true);
                    }

                    if(!isset($info['alipay_type']) || (isset($info['alipay_type']) && $info['alipay_type']== 1)){

                        $alipay = app('alipay.mobile');
                        if(!empty($info) && isset($info['partner_id'])){
                            $alipay->setPartner($info['partner_id']);
                        }
                        if(!empty($info) && isset($info['seller_id'])){
                            $alipay->setSellerId($info['seller_id']);
                        }
                        $alipay->setNotifyUrl(url('api/alipay/notify'));
                        $alipay->setOutTradeNo($order->code);
                        $alipay->setTotalFee($order->cash);
                        $alipay->setSubject($order->title);
                        $alipay->setBody($order->note);
                        return $this->formateResponse(1000, '确认充值', ['payParam' => $alipay->getPayPara()]);
                    }else{
                        $Client = new \AopClient();
                        $seller_id = $appId = '';
                        if(!empty($info) && isset($info['appId'])){
                            $appId = $info['appId'];
                        }
                        if(!empty($info) && isset($info['seller_id'])){
                            $seller_id = $info['seller_id'];
                        }
                        $content = [
                            'seller_id' => $seller_id,
                            'out_trade_no' => $order->code,
                            'timeout_express' => "30m",
                            'subject'      => $order->title,
                            'total_amount'    => $order->cash,
                            'product_code'    => 'QUICK_MSECURITY_PAY',
                        ];
                        $con = json_encode($content);

                        $param['app_id'] = $appId;
                        $param['method'] = 'alipay.trade.app.pay';
                        $param['charset'] = 'utf-8';
                        $param['sign_type'] = 'RSA';
                        $param['timestamp'] = date("Y-m-d H:i:s");
                        $param['version'] = '1.0';
                        $param['notify_url'] = url('api/alipay/notify');
                        $param['biz_content'] = $con;
                        $private_path = storage_path('app/alipay/rsa_private_key.pem');
                        $paramStr = $Client->getSignContent($param);
                        $sign = $Client->alonersaSign($paramStr, $private_path, 'RSA', true);
                        $param['sign'] = $sign;
                        $str = $Client->getSignContentUrlencode($param);
                        return $this->formateResponse(1000, '确认充值', ['payParam' => $str]);
                    }

                    



                    break;
                case 'wechat':
                    $gateway = Omnipay::gateway('WechatPay');
                    $configInfo = ConfigModel::getConfigByAlias('app_wechat');
                    $config = [];
                    if($configInfo && !empty($configInfo['rule'])){
                        $config = json_decode($configInfo['rule'],true);
                    }
                    if(isset($config['appId'])){
                        $gateway->setAppId($config['appId']);
                    }
                    if(isset($config['mchId'])){
                        $gateway->setMchId($config['mchId']);
                    }
                    if(isset($config['apiKey'])){
                        $gateway->setApiKey($config['apiKey']);
                    }
                    $gateway->setNotifyUrl(url('api/wechatpay/notify'));
                    $data = [
                        'body' => $order->title,
                        'out_trade_no' => $order->code,
                        'total_fee' => $order->cash*100, 
                        'spbill_create_ip' => Input::getClientIp(),
                        'fee_type' => 'CNY'
                    ];
                    $request = $gateway->purchase($data);
                    $response = $request->send();
                    if ($response->isSuccessful()) {
                        Log::info('微信支付订单编号'.$order->code);
                        return $this->formateResponse(1000, '确认充值', ['params' => $response->getAppOrderData()]);
                    }
                    break;
            }
        } else {
            return $this->formateResponse(1072, '订单生成失败');
        }
    }


}