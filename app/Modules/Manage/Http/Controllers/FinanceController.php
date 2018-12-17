<?php
namespace App\Modules\Manage\Http\Controllers;

use App\Http\Controllers\BasicController;
use App\Http\Controllers\ManageController;
use App\Http\Requests;
use App\Modules\Finance\Model\CashoutModel;
use App\Modules\Finance\Model\FinancialModel;
use App\Modules\Order\Model\OrderModel;
use App\Modules\Order\Model\SubOrderModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use App\Modules\User\Model\MessageReceiveModel;
use Guzzle\Http\Message\Response;
use Illuminate\Http\Request;
use Excel;
use Illuminate\Support\Facades\DB;

class FinanceController extends ManageController
{
    public function __construct()
    {
        parent::__construct();
        $this->initTheme('manage');
        $this->theme->set('manageType', 'finance');
    }

    
    public function financeList(Request $request)
    {
        $this->theme->setTitle('网站流水');
        $incomeArr = \CommonClass::incomeArr();
        $outcomeArr = \CommonClass::outcomeArr();
        $payType = \CommonClass::getPayType();
        $financeList = FinancialModel::select('id', 'action', 'pay_type', 'pay_account', 'pay_code', 'cash', 'uid', 'created_at');
        if ($request->get('action') == 3) {

            $financeList = $financeList->whereIn('action', $incomeArr);
        } elseif ($request->get('action') == 4) {

            $financeList = $financeList->whereIn('action', $outcomeArr);
        }


        if ($request->get('start')) {
            $start = preg_replace('/([\x80-\xff]*)/i', '', $request->get('start'));
            $start = date('Y-m-d H:i:s',strtotime($start));
            $financeList = $financeList->where('created_at', '>', $start);
        }
        if ($request->get('end')) {
            $end = preg_replace('/([\x80-\xff]*)/i', '', $request->get('end'));
            $end = date('Y-m-d H:i:s',strtotime($end));
            $financeList = $financeList->where('created_at', '<', $end);
        }
        $by = $request->get('by') ? $request->get('by') : 'id';
        $order = $request->get('order') ? $request->get('order') : 'desc';
        $paginate = $request->get('paginate') ? $request->get('paginate') : 10;

        $financeList = $financeList->orderBy($by, $order)->paginate($paginate);
        $arrUid= [];
        if ($financeList->total() > 0) {
            foreach ($financeList as $item) {
                $arrUid[] = $item->uid;
            }
            $arrUsername = UserModel::select('id', 'name')->whereIn('id', $arrUid)->get()->toArray();
            foreach ($financeList as $key => $item) {
                foreach ($arrUsername as $value) {
                    if ($value['id'] == $item->uid) {
                        $financeList[$key]['username'] = $value['name'];
                    }
                }
            }
        }

        $data = array(
            'start' => $request->get('start'),
            'end' => $request->get('end'),
            'income_arr' => $incomeArr,
            'outcome_arr' => $outcomeArr,
            'pay_type' => $payType
        );
        if ($financeList->total() > 0) {
            $data['finance'] = $financeList;
        } else {
            $data['finance'] = '';
        }
        $data['cashcount'] = FinancialModel::whereIn('action', $outcomeArr)->sum('cash');
        $search = [
            'start' => $request->get('start'),
            'end' => $request->get('end'),
        ];
        $data['search'] = $search;
        $data['action_type'] = \CommonClass::getFinanceAction();
        return $this->theme->scope('manage.financelist', $data)->render();
    }

    
    public function financeListExport(Request $request)
    {
        $param = $request->all();
        $finance = FinancialModel::select('financial.*', 'users.name')->leftJoin('users', 'financial.uid', '=', 'users.id')
            
;

        if (!empty($param['start']) && $param['start'] != 'NaN') {
            $start = preg_replace('/([\x80-\xff]*)/i', '', $request->get('start'));
            $start = date('Y-m-d H:i:s',strtotime($start));
            $finance = $finance->where('financial.created_at', '>', $start);
        }
        if (!empty($param['end']) && $param['end']!= 'NaN') {
            $end = preg_replace('/([\x80-\xff]*)/i', '', $request->get('end'));
            $end = date('Y-m-d H:i:s',strtotime($end));
            $finance = $finance->where('financial.created_at', '<', $end);
        }

        $data = [
            [
                '编号',
                '渠道类型',
                '收入/支出',
                '用户',
                '金额',
                '时间',
            ]
        ];
        $i = 0;
        $result = $finance->get()->chunk(100);
        $incomeArr = \CommonClass::incomeArr();
        $payType = \CommonClass::getPayType();
        foreach ($result as $key => $chunk) {
            foreach ($chunk as $k => $v) {
                if(in_array($v->action,$incomeArr)){
                    $v->action = '支出';
                }else{
                    $v->action = '收入';
                }
                if(in_array($v->pay_type,$payType)){
                    $v->pay_type = $payType[$v->pay_type];
                }
                $data[$i + 1] = [
                    $v->id,
                    $v->pay_type,
                    $v->action,
                    $v->name,
                    '￥' . $v->cash . '元',
                    $v->created_at
                ];
                $i++;
            }
        }
        Excel::create(iconv('UTF-8', 'GBK', '网站流水记录'), function ($excel) use ($data) {
            $excel->sheet('score', function ($sheet) use ($data) {
                $sheet->rows($data);

            });
        })->export('xls');


    }


    
    public function userFinanceListExport(Request $request)
    {
        $param = $request->all();
        $userFinance = FinancialModel::whereRaw('1 = 1');
        if (!empty($param['uid'])) {
            $userFinance = $userFinance->where('financial.uid', $param['uid']);
        }
        if (!empty($param['username'])) {
            $userFinance = $userFinance->where('users.name', $param['username']);
        }
        if (!empty($param['action'])) {
            $userFinance = $userFinance->where('financial.action', $param['action']);
        }
        if (!empty($param['start']) && $param['start'] != 'NaN') {
            $start = preg_replace('/([\x80-\xff]*)/i', '', $request->get('start'));
            $start = date('Y-m-d H:i:s',strtotime($start));
            $userFinance = $userFinance->where('financial.created_at', '>', $start);
        }
        if (!empty($param['end']) && $param['end']!= 'NaN') {
            $end = preg_replace('/([\x80-\xff]*)/i', '', $request->get('end'));
            $end = date('Y-m-d H:i:s',strtotime($end));
            $userFinance = $userFinance->where('financial.created_at', '<', $end);
        }
        $by = !empty($param['by']) ? $param['by'] : 'id';
        $order = !empty($param['order']) ? $param['order'] : 'desc';
        $result = $userFinance->leftJoin('user_detail', 'financial.uid', '=', 'user_detail.uid')
            ->leftJoin('users', 'financial.uid', '=', 'users.id')
            ->select('financial.*', 'user_detail.balance', 'users.name')
            ->orderBy($by, $order)->get()->chunk(100);

        $actionArr = \CommonClass::getFinanceAction();
        $data = [
            [
                '编号',
                '财务类型',
                '用户',
                '金额',
                '用户余额',
                '时间'
            ]
        ];
        $i = 0;
        foreach ($result as $chunk) {
            foreach ($chunk as $k => $v) {

                if(in_array($v->action,array_keys($actionArr))){
                    $v->action =  $actionArr[$v->action];
                }

                $data[$i + 1] = [
                    $v->id,
                    $v->action,
                    $v->name,
                    '￥' . $v->cash .'元',
                    $v->balance,
                    $v->created_at
                ];
                $i++;
            }
        }
        Excel::create(iconv('UTF-8', 'GBK', '用户流水记录'), function ($excel) use ($data) {
            $excel->sheet('score', function ($sheet) use ($data) {
                $sheet->rows($data);
            });
        })->export('xls');
    }


    
    public function userFinance(Request $request)
    {
        $this->theme->setTitle('用户流水');

        $userFinance = FinancialModel::whereRaw('1 = 1');

        if ($request->get('uid')) {
            $userFinance = $userFinance->where('financial.uid', $request->get('uid'));
        }
        if ($request->get('username')) {
            $userFinance = $userFinance->where('users.name', $request->get('username'));
        }
        if ($request->get('action')) {
            $userFinance = $userFinance->where('financial.action', $request->get('action'));
        }
        if ($request->get('start')) {
            $start = preg_replace('/([\x80-\xff]*)/i', '', $request->get('start'));
            $start = date('Y-m-d H:i:s',strtotime($start));
            $userFinance = $userFinance->where('financial.created_at', '>', $start);
        }
        if ($request->get('end')) {
            $end = preg_replace('/([\x80-\xff]*)/i', '', $request->get('end'));
            $end = date('Y-m-d H:i:s',strtotime($end));
            $userFinance = $userFinance->where('financial.created_at', '<', $end);
        }
        $by = $request->get('by') ? $request->get('by') : 'id';
        $order = $request->get('order') ? $request->get('order') : 'desc';
        $paginate = $request->get('paginate') ? $request->get('paginate') : 10;

        $list = $userFinance->leftJoin('user_detail', 'financial.uid', '=', 'user_detail.uid')
            ->leftJoin('users', 'financial.uid', '=', 'users.id')
            ->select('financial.*', 'user_detail.balance', 'users.name')
            ->orderBy($by, $order)->paginate($paginate);

        $actionArr = \CommonClass::getFinanceAction();

        $data = array(
            'uid' => $request->get('uid'),
            'username' => $request->get('username'),
            'action' => $request->get('action'),
            'paginate' => $request->get('paginate'),
            'order' => $request->get('order'),
            'by' => $request->get('by'),
            'start' => $request->get('start'),
            'end' => $request->get('end'),
            'list' => $list,
            'action_arr' => $actionArr
        );
        $search = [
            'uid' => $request->get('uid'),
            'username' => $request->get('username'),
            'action' => $request->get('action'),
            'paginate' => $request->get('paginate'),
            'order' => $request->get('order'),
            'by' => $request->get('by'),
            'start' => $request->get('start'),
            'end' => $request->get('end'),
        ];
        $data['search'] = $search;

        return $this->theme->scope('manage.userfinance', $data)->render();
    }

    
    public function cashoutList(Request $request)
    {
        $this->theme->setTitle('提现记录');

        $cashout = CashoutModel::whereRaw('1 = 1');
        if ($request->get('id')) {
            $cashout = $cashout->where('cashout.id', $request->get('id'));
        }
        if ($request->get('username')) {
            $cashout = $cashout->where('users.name', $request->get('username'));
        }
        if ($request->get('cashout_type')) {
            $cashout = $cashout->where('cashout.cashout_type', $request->get('cashout_type'));
        }
        if ($request->get('start')) {
            $start = preg_replace('/([\x80-\xff]*)/i', '', $request->get('start'));
            $start = date('Y-m-d H:i:s',strtotime($start));
            $cashout = $cashout->where('cashout.created_at', '>', $start);
        }
        if ($request->get('end')) {
            $end = preg_replace('/([\x80-\xff]*)/i', '', $request->get('end'));
            $end = date('Y-m-d H:i:s',strtotime($end));
            $cashout = $cashout->where('cashout.created_at', '<', $end);
        }

        $by = $request->get('by') ? $request->get('by') : 'id';
        $order = $request->get('order') ? $request->get('order') : 'desc';
        $paginate = $request->get('paginate') ? $request->get('paginate') : 10;

        $list = $cashout->leftJoin('users', 'cashout.uid', '=', 'users.id')
            ->leftJoin('user_detail', 'cashout.uid', '=', 'user_detail.uid')
            ->select('cashout.*', 'users.name', 'user_detail.realname')
            ->orderBy($by, $order)->paginate($paginate);

        $data = array(
            'id' => $request->get('id'),
            'username' => $request->get('username'),
            'cashout_type' => $request->get('cashout_type'),
            'paginate' => $request->get('paginate'),
            'order' => $request->get('order'),
            'by' => $request->get('by'),
            'start' => $request->get('start'),
            'end' => $request->get('end'),
            'list' => $list
        );
        $search = [
            'id' => $request->get('id'),
            'username' => $request->get('username'),
            'cashout_type' => $request->get('cashout_type'),
            'paginate' => $request->get('paginate'),
            'order' => $request->get('order'),
            'by' => $request->get('by'),
            'start' => $request->get('start'),
            'end' => $request->get('end'),
        ];
        $data['search'] = $search;

        return $this->theme->scope('manage.cashoutlist', $data)->render();
    }

    
    public function cashoutHandle($id, $action)
    {
        $info = CashoutModel::where('id', $id)->first();
        $userInfo=DB::table('users')->where('id',$info['uid'])->select('name')->first();
            switch ($action) {
                case 'pass':
				    $title="提现审核通过";
                    $status = $info->update(array('status' => 1));
					$content="尊敬的".$userInfo->name ."，您的提现已审核通过，请注意查看到账信息，
					          如有疑问请致电客服。到账金额：".$info['cash']."，提现手续费：".$info['fees'];
                    break;
                case 'deny':
				    $title="提现审核未通过";
                    $status = CashoutModel::cashoutRefund($id);
					$content="尊敬的".$userInfo->name ."很遗憾您的提现审核未通过，如有疑问请致电客服。";
                    break;
            }
            if ($status){
				
				$arr = array(
					'message_title' => $title,
					'message_content' => $content,
					'message_type' => 2,
					'fs_id' => $this->manager->id,
					'js_id' => $info['uid'],
					'receive_time' => date('Y-m-d H:i:s',time())
				);
                MessageReceiveModel::insert($arr);
                return redirect('manage/cashoutList')->with(array('message' => '操作成功'));
            }
			   return redirect('manage/cashoutList')->with(array('message' => '操作失败'));
    }

    
    public function cashoutInfo($id)
    {
        $info = CashoutModel::where('cashout.id', $id)
            ->leftJoin('user_detail', 'cashout.uid', '=', 'user_detail.uid')
            ->select('cashout.*', 'user_detail.realname')
            ->first();

        if (!empty($info)) {
            $data = array(
                'info' => $info
            );
            return $this->theme->scope('manage.cashoutinfo', $data)->render();
        }
    }

    
    public function getUserRecharge()
    {
        return $this->theme->scope('manage.recharge')->render();
    }


    
    public function postUserRecharge(Request $request)
    {
        $account = UserModel::where('id', $request->get('uid'))->orWhere('name', $request->get('username'))->first();
        if (!empty($account)) {
            $action = $request->get('action');
            switch ($action) {
                case 'increment':
                    
                    $status = '';
                    break;
                case 'decrement':
                    
                    $status = '';
                    break;
            }
            if ($status)
                return redirect('manage/recharge')->with(array('message' => '操作成功'));
        }
    }

    
    public function verifyUser($param)
    {
        $user = UserModel::where('id', $param)->orWhere('name', $param)->first();
        $data = null;
        if (!empty($user)) {
            $userInfo = UserDetailModel::select('balance')->where('uid', $user->id)->first();
            $data = array(
                'username' => $user->name,
                'balance' => $userInfo->balance
            );
        }
        return \CommonClass::formatResponse('验证完成', 200, $data);
    }

    
    public function rechargeList(Request $request)
    {
        $this->theme->setTitle('充值记录');

        $recharge = OrderModel::whereNull('order.task_id')->where('order.status', 0);
        if ($request->get('code')) {
            $recharge = $recharge->where('order.code', $request->get('code'));
        }
        if ($request->get('username')) {
            $recharge = $recharge->where('users.name', $request->get('username'));
        }
        if ($request->get('start')) {
            $start = preg_replace('/([\x80-\xff]*)/i', '', $request->get('start'));
            $start = date('Y-m-d H:i:s',strtotime($start));
            $recharge = $recharge->where('order.created_at', '>', $start);
        }
        if ($request->get('end')) {
            $end = preg_replace('/([\x80-\xff]*)/i', '', $request->get('end'));
            $end = date('Y-m-d H:i:s',strtotime($end));
            $recharge = $recharge->where('order.created_at', '<', $end);
        }

        $by = $request->get('by') ? $request->get('by') : 'code';
        $order = $request->get('order') ? $request->get('order') : 'desc';
        $paginate = $request->get('paginate') ? $request->get('paginate') : 10;

        $list = $recharge->leftJoin('users', 'order.uid', '=', 'users.id')
            ->select('order.*', 'users.name')
            ->orderBy($by, $order)->paginate($paginate);

        $data = array(
            'code' => $request->get('code'),
            'username' => $request->get('username'),
            'start' => $request->get('start'),
            'end' => $request->get('end'),
            'order' => $request->get('order'),
            'by' => $request->get('by'),
            'paginate' => $request->get('paginate'),
            'list' => $list
        );
        $search = [
            'code' => $request->get('code'),
            'username' => $request->get('username'),
            'start' => $request->get('start'),
            'end' => $request->get('end'),
            'order' => $request->get('order'),
            'by' => $request->get('by'),
            'paginate' => $request->get('paginate'),
        ];
        $data['search'] = $search;

        return $this->theme->scope('manage.rechargelist', $data)->render();
    }

    
    public function confirmRechargeOrder($order)
    {
        $order = OrderModel::where('code', $order)->first();
        if (!empty($order)) {
            $status = OrderModel::adminRecharge($order);
            if ($status) {
                return redirect('manage/rechargeList')->with(array('message' => '操作成功'));
            }
        }
    }

    
    public function financeStatement()
    {
        $this->theme->setTitle('网站收支');
        $now = strtotime(date('Y-m-d', time()));
        $oneDay = 24 * 60 * 60;
        
        $maxDay = 7;
        for ($i = 0; $i < $maxDay; $i++) {
            $timeArr[$i]['min'] = date('Y-m-d H:i:s', ($now - $oneDay * ($i + 1)));
            $timeArr[$i]['max'] = date('Y-m-d H:i:s', ($now - $oneDay * $i));
        }
        
        $timeArr = array_reverse($timeArr);

        foreach ($timeArr as $k => $v) {
            $dateArr[] = date('m', strtotime($timeArr[$k]['min'])) . '月' . date('d', strtotime($timeArr[$k]['min'])) . '日';
        }
        
        $arrFinance = FinancialModel::select('action', 'cash', 'created_at')
            ->where('created_at', '<', $timeArr[6]['max'])
            ->where('created_at', '>', $timeArr[1]['min'])->get();
        
        $arrTask = OrderModel::select('created_at', 'cash')->whereNotNull('task_id')
            ->where('created_at', '<', $timeArr[6]['max'])
            ->where('created_at', '>', $timeArr[1]['min'])->get();
        
        $arrService = SubOrderModel::select('created_at', 'cash')->where('product_type', 3)
            ->where('created_at', '<', $timeArr[6]['max'])
            ->where('created_at', '>', $timeArr[1]['min'])->get();

        $arr = array();
        
        if (!empty($arrFinance)) {
            foreach ($arrFinance as $item) {
                switch ($item->action) {
                    case 3:
                        for ($i = 0; $i < $maxDay; $i++) {
                            if ($item->created_at > $timeArr[$i]['min'] && $item->created_at < $timeArr[$i]['max']) {
                                $arr['in'][$i][] = $item->cash;
                            }
                        }
                        break;
                    case 4:
                        for ($i = 0; $i < $maxDay; $i++) {
                            if ($item->created_at > $timeArr[$i]['min'] && $item->created_at < $timeArr[$i]['max']) {
                                $arr['out'][$i][] = $item->cash;
                            }
                        }
                        break;
                }
            }
        }
        if (!empty($arrTask)) {
            foreach ($arrTask as $item) {
                for ($i = 0; $i < $maxDay; $i++) {
                    if ($item->created_at > $timeArr[$i]['min'] && $item->created_at < $timeArr[$i]['max']) {
                        $arr['task'][$i][] = $item->cash;
                    }
                }
            }
        }
        if (!empty($arrService)) {
            foreach ($arrService as $item) {
                for ($i = 0; $i < $maxDay; $i++) {
                    if ($item->created_at > $timeArr[$i]['min'] && $item->created_at < $timeArr[$i]['max']) {
                        $arr['tool'][$i][] = $item->cash;
                    }
                }
            }
        }
        
        if (!empty($arr)) {
            if (!empty($arr['in'])) {
                for ($i = 0; $i < $maxDay; $i++) {
                    if (isset($arr['in'][$i])) {
                        $arr['in'][$i] = array_sum($arr['in'][$i]);
                    } else {
                        $arr['in'][$i] = 0;
                    }
                }
            } else {
                for ($i = 0; $i < $maxDay; $i++) {
                    $arr['in'][$i] = 0;
                }
            }
            if (!empty($arr['out'])) {
                for ($i = 0; $i < $maxDay; $i++) {
                    if (isset($arr['out'][$i])) {
                        $arr['out'][$i] = array_sum($arr['out'][$i]);
                    } else {
                        $arr['out'][$i] = 0;
                    }
                }
            } else {
                for ($i = 0; $i < $maxDay; $i++) {
                    $arr['out'][$i] = 0;
                }
            }
            if (!empty($arr['task'])) {
                for ($i = 0; $i < $maxDay; $i++) {
                    if (isset($arr['task'][$i])) {
                        $arr['task'][$i] = array_sum($arr['task'][$i]);
                    } else {
                        $arr['task'][$i] = 0;
                    }
                }
            } else {
                for ($i = 0; $i < $maxDay; $i++) {
                    $arr['task'][$i] = 0;
                }
            }
            if (!empty($arr['tool'])) {
                for ($i = 0; $i < $maxDay; $i++) {
                    if (isset($arr['tool'][$i])) {
                        $arr['tool'][$i] = array_sum($arr['tool'][$i]);
                    } else {
                        $arr['tool'][$i] = 0;
                    }
                }
            } else {
                for ($i = 0; $i < $maxDay; $i++) {
                    $arr['tool'][$i] = 0;
                }
            }
        } else {
            for ($i = 0; $i < $maxDay; $i++) {
                $arr['in'][$i] = 0;
                $arr['out'][$i] = 0;
                $arr['task'][$i] = 0;
                $arr['tool'][$i] = 0;
            }
        }
        

        
        $finance = [
            'in' => [
                [1, $arr['in'][0]],
                [4, $arr['in'][1]],
                [7, $arr['in'][2]],
                [10, $arr['in'][3]],
                [13, $arr['in'][4]],
                [16, $arr['in'][5]],
                [19, $arr['in'][6]]
            ],
            'out' => [
                [2, $arr['out'][0]],
                [5, $arr['out'][1]],
                [8, $arr['out'][2]],
                [11, $arr['out'][3]],
                [14, $arr['out'][4]],
                [17, $arr['out'][5]],
                [20, $arr['out'][6]]
            ],
            'task' => [
                [1, $arr['task'][0]],
                [4, $arr['task'][1]],
                [7, $arr['task'][2]],
                [10, $arr['task'][3]],
                [13, $arr['task'][4]],
                [16, $arr['task'][5]],
                [19, $arr['task'][6]]
            ],
            'tool' => [
                [2, $arr['tool'][0]],
                [5, $arr['tool'][1]],
                [8, $arr['tool'][2]],
                [11, $arr['tool'][3]],
                [14, $arr['tool'][4]],
                [17, $arr['tool'][5]],
                [20, $arr['tool'][6]]
            ]
        ];
        
        $broken = [
            'cash' => [
                [0, $arr['in'][0]],
                [1, $arr['in'][1]],
                [2, $arr['in'][2]],
                [3, $arr['in'][3]],
                [4, $arr['in'][4]],
                [5, $arr['in'][5]],
                [6, $arr['in'][6]],
            ],
            'out' => [
                [0, $arr['out'][0]],
                [1, $arr['out'][1]],
                [2, $arr['out'][2]],
                [3, $arr['out'][3]],
                [4, $arr['out'][4]],
                [5, $arr['out'][5]],
                [6, $arr['out'][6]],
            ],
            'task' => [
                [0, $arr['task'][0]],
                [1, $arr['task'][1]],
                [2, $arr['task'][2]],
                [3, $arr['task'][3]],
                [4, $arr['task'][4]],
                [5, $arr['task'][5]],
                [6, $arr['task'][6]],
            ],
            'tool' => [
                [0, $arr['tool'][0]],
                [1, $arr['tool'][1]],
                [2, $arr['tool'][2]],
                [3, $arr['tool'][3]],
                [4, $arr['tool'][4]],
                [5, $arr['tool'][5]],
                [6, $arr['tool'][6]],
            ]
        ];
        $data = [
            'finance' => json_encode($finance),
            'broken' => json_encode($broken),
            'dateArr' => json_encode($dateArr)
        ];
        return $this->theme->scope('manage.financeStatement', $data)->render();
    }

    
    public function financeRecharge(Request $request)
    {
        $this->theme->setTitle('充值记录');
        $list = FinancialModel::select('financial.id', 'users.name', 'financial.pay_type', 'financial.pay_account', 'financial.cash', 'financial.created_at')
            ->leftJoin('users', 'users.id', '=', 'financial.uid')->where('financial.action', 3);
        if ($request->get('type')) {
            switch ($request->get('type')) {
                case 'alipay':
                    $list = $list->where('financial.pay_type', 2);
                    break;
                case 'wechat':
                    $list = $list->where('financial.pay_type', 3);
                    break;
                case 'bankunion':
                    $list = $list->where('financial.pay_type', 4);
                    break;
            }
        }
        if ($request->get('start')) {
            $start = preg_replace('/([\x80-\xff]*)/i', '', $request->get('start'));
            $start = date('Y-m-d H:i:s',strtotime($start));
            $list = $list->where('financial.created_at', '>', $start);
        }
        if ($request->get('end')) {
            $end = preg_replace('/([\x80-\xff]*)/i', '', $request->get('end'));
            $end = date('Y-m-d H:i:s',strtotime($end));
            $list = $list->where('financial.created_at', '<', $end);
        }

        $count = $list->count();
        $sum = $list->sum('financial.cash');

        $list = $list->orderBy('financial.id', 'DESC')->paginate(10);
        $payType = \CommonClass::getPayType();
        $data = [
            'list' => $list,
            'count' => $count,
            'sum' => $sum,
            'start' => $request->get('start'),
            'end' => $request->get('end'),
            'type' => $request->get('type'),
            'pay_type' => $payType
        ];
        $search = [
            'start' => $request->get('start'),
            'end' => $request->get('end'),
            'type' => $request->get('type')
        ];
        $data['search'] = $search;
        return $this->theme->scope('manage.financeRecharge', $data)->render();
    }

    
    public function financeRechargeExport($param)
    {
        $param = \CommonClass::getParamByQueryString($param);

        $list = FinancialModel::select('financial.id', 'users.name', 'financial.pay_type', 'financial.pay_account', 'financial.cash', 'financial.created_at')
            ->leftJoin('users', 'users.id', '=', 'financial.uid')->where('financial.action', 3);
        if ($param['type'][0]) {
            switch ($param['type'][0]) {
                case 'alipay':
                    $list = $list->where('financial.pay_type', 2);
                    break;
                case 'wechat':
                    $list = $list->where('financial.pay_type', 3);
                    break;
                case 'bankunion':
                    $list = $list->where('financial.pay_type', 4);
                    break;
            }
        }
        if ($param['start'][0]) {
            $start = preg_replace('/([\x80-\xff]*)/i', '', $param['start'][0]);
            $start = date('Y-m-d H:i:s',strtotime($start));
            $list = $list->where('financial.created_at', '>', $start);
        }
        if ($param['end'][0]) {
            $end = preg_replace('/([\x80-\xff]*)/i', '', $param['end'][0]);
            $end = date('Y-m-d H:i:s',strtotime($end));
            $list = $list->where('financial.created_at', '<', $end);
        }

        $count = $list->count();
        $sum = $list->sum('financial.cash');
        $list = $list->get()->chunk(100);
        $data = [
            [
                '编号',
                '用户名',
                '充值方式',
                '充值账号',
                '金额',
                '充值时间'
            ]
        ];
        $i = 0;
        foreach ($list as $chunk) {
            foreach ($chunk as $k => $v) {
                switch ($v->pay_type) {
                    case 2:
                        $v->action = '支付宝';
                        break;
                    case 3:
                        $v->action = '微信';
                        break;
                    case 4:
                        $v->action = '银联';
                        break;
                }
                $data[$i + 1] = [
                    $v->id,
                    $v->name,
                    $v->action,
                    $v->pay_account,
                    '￥' . $v->cash . '元',
                    $v->created_at,
                ];
                $i++;
            }
        }
        $data[$i + 1] = [
            '总计', '', $count, '', $sum, ''
        ];
        Excel::create(iconv('UTF-8', 'GBK', '充值记录'), function ($excel) use ($data) {
            $excel->sheet('score', function ($sheet) use ($data) {
                $sheet->rows($data);
            });
        })->export('xls');
    }

    
    public function financeWithdraw(Request $request)
    {
        $this->theme->setTitle('提现记录');
        $list = CashoutModel::select('cashout.id', 'users.name', 'cashout.cashout_type', 'cashout.cashout_account', 'cashout.cash',
            'cashout.real_cash', 'cashout.fees', 'cashout.created_at', 'cashout.updated_at')
            ->leftJoin('users', 'cashout.uid', '=', 'users.id')->where('cashout.status', 1);

        if ($request->get('type')) {
            switch ($request->get('type')) {
                case 'alipay':
                    $list = $list->where('cashout.cashout_type', 1);
                    break;
                case 'bank':
                    $list = $list->where('cashout.cashout_type', 2);
                    break;
            }
        }
        if ($request->get('start')) {
            $start = preg_replace('/([\x80-\xff]*)/i', '', $request->get('start'));
            $start = date('Y-m-d H:i:s',strtotime($start));
            $list = $list->where('cashout.updated_at', '>', $start);
        }
        if ($request->get('end')) {
            $end = preg_replace('/([\x80-\xff]*)/i', '', $request->get('end'));
            $end = date('Y-m-d H:i:s',strtotime($end));
            $list = $list->where('cashout.updated_at', '<', $end);
        }
        
        $count = $list->count();
        
        $cashSum = $list->sum('cashout.cash');
        
        $realCashSum = $list->sum('cashout.real_cash');
        
        $feesSum = $list->sum('cashout.fees');
        $list = $list->orderBy('cashout.id', 'DESC')->paginate(10);
        $data = [
            'list' => $list,
            'start' => $request->get('start'),
            'end' => $request->get('end'),
            'type' => $request->get('type'),
            'count' => $count,
            'cashSum' => $cashSum,
            'realCashSum' => $realCashSum,
            'feesSum' => $feesSum
        ];
        $search = [
            'start' => $request->get('start'),
            'end' => $request->get('end'),
            'type' => $request->get('type'),
        ];
        $data['search'] = $search;

        return $this->theme->scope('manage.financeWithdraw', $data)->render();
    }


    public function financeWithdrawExport($param)
    {
        $param = \CommonClass::getParamByQueryString($param);

        $list = CashoutModel::select('cashout.id', 'users.name', 'cashout.cashout_type', 'cashout.cashout_account', 'cashout.cash',
            'cashout.real_cash', 'cashout.fees', 'cashout.created_at', 'cashout.updated_at')
            ->leftJoin('users', 'cashout.uid', '=', 'users.id')->where('cashout.status', 1);

        if ($param['type'][0]) {
            switch ($param['type'][0]) {
                case 'alipay':
                    $list = $list->where('cashout.cashout_type', 1);
                    break;
                case 'bank':
                    $list = $list->where('cashout.cashout_type', 2);
                    break;
            }
        }
        if ($param['start'][0]) {
            $start = preg_replace('/([\x80-\xff]*)/i', '', $param['start'][0]);
            $start = date('Y-m-d H:i:s',strtotime($start));
            $list = $list->where('cashout.updated_at', '>', $start);
        }
        if ($param['end'][0]) {
            $end = preg_replace('/([\x80-\xff]*)/i', '', $param['end'][0]);
            $end = date('Y-m-d H:i:s',strtotime($end));
            $list = $list->where('cashout.updated_at', '<', $end);
        }
        
        $count = $list->count();
        
        $cashSum = $list->sum('cashout.cash');
        
        $realCashSum = $list->sum('cashout.real_cash');
        
        $feesSum = $list->sum('cashout.fees');

        $list = $list->get()->chunk(100);
        $data = [
            [
                '编号',
                '用户名',
                '提现方式',
                '提现账号',
                '提现金额',
                '到账金额',
                '手续费',
                '提现时间',
            ]
        ];
        $i = 0;
        foreach ($list as $chunk) {
            foreach ($chunk as $k => $v) {
                switch ($v->cashout_type) {
                    case 1:
                        $v->action = '支付宝';
                        break;
                    case 2:
                        $v->action = '银行卡';
                        break;
                }
                $data[$i + 1] = [
                    $v->id,
                    $v->name,
                    $v->action,
                    $v->cashout_account,
                    $v->cash,
                    $v->real_cash,
                    $v->fees,
                    $v->created_at
                ];
                $i++;
            }
        }
        $data[$i + 1] = [
            '总计', '', $count.'次', '', $cashSum, $realCashSum, $feesSum, ''
        ];
        Excel::create(iconv('UTF-8', 'GBK', '提现记录'), function ($excel) use ($data) {
            $excel->sheet('score', function ($sheet) use ($data) {
                $sheet->rows($data);
            });
        })->export('xls');
    }

    
    public function financeProfit(Request $request)
    {
        $this->theme->setTitle('利润统计');

        $from = $request->get('from') ? $request->get('from') : 'task';
        if ($request->get('start')) {
            $start = preg_replace('/([\x80-\xff]*)/i', '', $request->get('start'));
            $start = date('Y-m-d H:i:s',strtotime($start));
        }
        if ($request->get('end')) {

            $end = preg_replace('/([\x80-\xff]*)/i', '', $request->get('end'));
            $end = date('Y-m-d H:i:s',strtotime($end));
        }

        switch ($from) {
            case 'task':
                $list = OrderModel::select('order.task_id', 'users.name', 'order.cash', 'order.created_at')
                    ->whereNotNull('order.task_id')->leftJoin('users', 'order.uid', '=', 'users.id')->where('order.status', 1)
                    ->orderBy('order.created_at', 'DESC');
                if (isset($start)) {
                    $list = $list->where('order.created_at', '>', $start);
                }
                if (isset($end)) {
                    $list = $list->where('order.created_at', '<', $end);
                }
                $sum = $list->sum('order.cash');
                break;
            case 'tool':
                $list = SubOrderModel::select('users.name', 'sub_order.cash', 'sub_order.created_at')
                    ->where('sub_order.product_type', 3)->leftJoin('users', 'sub_order.uid', '=', 'users.id')
                    ->where('sub_order.status', 1)->orderBy('sub_order.created_at', 'DESC');
                if (isset($start)) {
                    $list = $list->where('sub_order.created_at', '>', $start);
                }
                if (isset($end)) {
                    $list = $list->where('sub_order.created_at', '<', $end);
                }
                $sum = $list->sum('sub_order.cash');
                break;
            case 'cashout':
                $list = CashoutModel::select('cashout.cash', 'cashout.real_cash', 'cashout.fees', 'cashout.created_at', 'users.name')
                    ->where('cashout.status', 1)->leftJoin('users', 'users.id', '=', 'cashout.uid')
                    ->orderBy('cashout.created_at', 'DESC');
                if (isset($start)) {
                    $list = $list->where('cashout.created_at', '>', $start);
                }
                if (isset($end)) {
                    $list = $list->where('cashout.created_at', '<', $end);
                }
                $sum = $list->sum('cashout.fees');
                break;
        }

        $list = $list->paginate(10);
        $data = [
            'list' => $list,
            'from' => $from,
            'start' => $request->get('start'),
            'end' => $request->get('end'),
            'sum' => $sum
        ];
        $search = [
            'from' => $from,
            'start' => $request->get('start'),
            'end' => $request->get('end'),
        ];
        $data['search'] = $search;

        return $this->theme->scope('manage.financeProfit', $data)->render();
    }
}
