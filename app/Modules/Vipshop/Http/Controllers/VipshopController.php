<?php

namespace App\Modules\Vipshop\Http\Controllers;

use App\Http\Controllers\IndexController;
use App\Http\Requests;
use App\Modules\Advertisement\Model\AdTargetModel;
use App\Modules\Finance\Model\FinancialModel;
use App\Modules\Manage\Model\ConfigModel;
use App\Modules\Manage\Model\FeedbackModel;
use App\Modules\Order\Model\ShopOrderModel;
use App\Modules\Shop\Models\ShopModel;
use App\Modules\Task\Model\TaskCateModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use App\Modules\User\Model\UserMoreModel;
use App\Modules\Vipshop\Http\Requests\PayOrderRequest;
use App\Modules\Vipshop\Models\InterviewModel;
use App\Modules\Vipshop\Models\PackageModel;
use App\Modules\Vipshop\Models\PackagePrivilegesModel;
use App\Modules\Vipshop\Models\PrivilegesModel;
use App\Modules\Vipshop\Models\ShopPackageModel;
use App\Modules\Vipshop\Models\VipshopOrderModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use DB;
use Omnipay;
use QrCode;
use Validator;
use Cache;

class VipshopController extends IndexController
{
    public function __construct()
    {
        parent::__construct();
        $this->user = Auth::user();
        $this->initTheme('vipshop');

        $touchMe = ConfigModel::getConfigByAlias('vip_shop_config');
        if($touchMe && !empty($touchMe['rule'])){
            $touchMe = json_decode($touchMe['rule'], true);
        }else{
            $touchMe = [
                'hot_line' => '',
                'logo1' => '',
                'logo2' => ''
            ];
        }
        $this->theme->set('vip_touch', $touchMe);
    }

    
    public function Index()
    {
        
        $ad = AdTargetModel::getAdInfo('VIP_TOP_SLIDE');
        
		$NavName= \CommonClass::getNavName('/vipshop');
		if(!$NavName){
			$NavName="VIP特权";
		}
        
        $arrPackage = PackageModel::where('status', 0)->where('type',0)
            ->orderBy('list', 'asc')->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()->toArray();
        if (!empty($arrPackage)) {
            foreach ($arrPackage as $k => $v) {
                $arrPackage[$k]['price'] = 0;
                if(is_array(json_decode($v['price_rules'], true))){
                    $arrPackage[$k]['price'] = collect(array_pluck(json_decode($v['price_rules'], true), 'cash'))->sort()->first();
                }
            }
        }
        
        $arrPrivilege = PrivilegesModel::select('ico', 'title', 'desc')
            ->where('status', 0)
            ->orderBy('list', 'desc')->limit(6)->get();

        
        $arrVipshop = ShopPackageModel::select('shop.shop_name', 'package.logo', 'shop.shop_pic','shop.id')
            ->join('shop', 'shop.id', '=', 'shop_package.shop_id')
            ->join('package', 'package.id', '=', 'shop_package.package_id')
            ->where('shop_package.status', 0)->orderBy('shop_package.created_at', 'desc')
            ->limit(15) ->get();

        
        $arrInterview = InterviewModel::select('interview.title', 'interview.desc', 'shop.id', 'shop.shop_pic', 'shop.shop_name', 'shop.uid', 'interview.id as vid')
            ->join('shop', 'shop.id', '=', 'interview.shop_id')
            ->orderBy('list', 'desc')->limit(4)->get();

        $data = [
            'ad' => $ad,
            'package_list' => $arrPackage,
            'privilege_list' => $arrPrivilege,
            'vishop_list' => $arrVipshop,
            'interview_list' => $arrInterview,
			'NavName'    =>$NavName
        ];
        $this->theme->set('vipactive', 'vipindex');
        return $this->theme->scope('vipshop.index', $data)->render();
    }

    
    public function Page()
    {
        $ad = AdTargetModel::getAdInfo('VIP_TOP_SLIDE');

        $perPage = 15;
        
        $arrInterview = InterviewModel::select('interview.title', 'interview.desc', 'shop.id', 'shop.shop_pic', 'shop.shop_name', 'shop.uid', 'interview.id as vid')
            ->leftJoin('shop', 'shop.id', '=', 'interview.shop_id')
            ->orderBy('list', 'desc');

        $count = $arrInterview->count();

        $list = $arrInterview->paginate($perPage);

        
        $userDetail = [];
        $user = Auth::User();
        if($user){
            $userDetail = UserDetailModel::where('uid',$user->id)->select('uid')->first();
        }
        $data = [
            'ad' => $ad,
            'list' => $arrInterview,
            'count' => $count,
            'list' => $list,
            'page' => Input::get('page') ? Input::get('page') : 1,
            'per_page' => $perPage,
            'userDetail' => $userDetail
        ];
        $this->theme->set('vipactive', 'interview');
        return $this->theme->scope('vipshop.page', $data)->render();
    }

    
    public function details($id)
    {
        $info = InterviewModel::findOrFail($id);

        $info->increment('view_count', 1);

        $sideList = InterviewModel::orderBy('list', 'desc')->limit(5)
            ->get(['title', 'id', 'desc', 'shop_cover']);

        $arrInterviewId = $sideList->map(function ($v, $k) {
            $id = $v->id;
            return $id;
        });

        $headId = $arrInterviewId->first(function ($k, $v) use ($id) {
            return $v > $id;
        });

        $nextId = $arrInterviewId->first(function ($k, $v) use ($id) {
            return $v < $id;
        });

        $headInfo = InterviewModel::find($headId);

        $nextInfo = InterviewModel::find($nextId);

        $data = [
            'info' => $info,
            'side_list' => $sideList,
            'head_info' => $headInfo,
            'next_info' => $nextInfo
        ];
        $this->theme->set('vipactive', 'interview');
        return $this->theme->scope('vipshop.details', $data)->render();
    }

    
    public function getPayvip()
    {
        $packages = PackageModel::where('status', 0)
            ->orderBy('list', 'asc')
            ->get(['id', 'title', 'logo', 'price_rules'])->toArray();

        $arrPackageId = collect($packages)->map(function ($item, $key) {
            return $item['id'];
        })->toArray();

        $privileges = PackagePrivilegesModel::whereIn('package_privileges.package_id', $arrPackageId)
            ->where('privileges.status', 0)
            ->leftJoin('privileges', 'package_privileges.privileges_id', '=', 'privileges.id')
            ->orderBy('privileges.list', 'desc')
            ->get(['package_privileges.package_id', 'privileges.id', 'privileges.title', 'privileges.desc', 'privileges.ico','package_privileges.rule'])
            ->toArray();

        $list = collect($packages)->map(function ($value, $key) use ($privileges) {
            $value['privileges'] = collect($privileges)->map(function ($v, $k) use ($value) {
                if ($value['id'] == $v['package_id']) {
                    return $v;
                }
            })->toArray();
            return $value;
        });

        $list->transform(function ($v, $k) {
            $v['price_rules'] = json_decode($v['price_rules'], true);
            $v['min_price'] = collect($v['price_rules'])->sortBy('cash')->first()['cash'];
            return $v;
        });

        $data = [
            'list' => $list
        ];
        $this->theme->set('vipactive', 'payvip');
        return $this->theme->scope('vipshop.payvip', $data)->render();
    }

    
    public function postPayvip()
    {
        $packageId = Input::get('packag_id');
        $ruleId = Input::get('price_rule_id');

        $package = PackageModel::findOrFail($packageId);

        $priceRules = json_decode($package->price_rules, true)[$ruleId];

        $code = ShopOrderModel::randomCode(Auth::id(), 'vs');

        $shopInfo = ShopModel::where('uid', Auth::id())->where('status', '1')->first();

        if (empty($shopInfo)) {
            return back()->with(['message' => '请先开启店铺']);
        }

        



        $data = [
            'code' => $code,
            'cash' => $priceRules['cash'],
            'title' => '开通vip店铺',
            'uid' => Auth::id(),
            'shop_id' => $shopInfo->id,
            'package_id' => $packageId,
            'time_period' => $priceRules['time_period']
        ];
        $status = VipshopOrderModel::create($data);
        if ($status) {
            session(['vipshopcode' => $status->code]);
            return redirect('vipshop/vipPayorder');
        }


    }


    public function vipinfo()
    {
        
        $packages = PackageModel::where('status', 0)
            ->orderBy('list', 'desc')
            ->get();

        $packages->each(function ($item, $key) {
            $ruleInfo = collect(json_decode($item['price_rules'], true))->sortBy('cash')->first();

            if (!empty($ruleInfo) && isset($ruleInfo['cash'])) {
                $item['price'] = $ruleInfo['cash'];
            }
        });

        $privileges = PrivilegesModel::where('status', 0)->orderBy('list', 'desc')->get(['id', 'title', 'desc']);

        $arrStatus = [];

        foreach ($privileges as $key => $item) {

            $packagesPrivileges = PackagePrivilegesModel::where('package_privileges.privileges_id', $item['id'])->leftJoin('privileges','privileges.id','=','package_privileges.privileges_id')->get(['package_privileges.package_id','package_privileges.rule','privileges.code'])->toArray();

            $packageId = collect($packagesPrivileges)->pluck('package_id')->toArray();

            $packagesPrivilegesArr = \CommonClass::setArrayKey($packagesPrivileges,'package_id');

            $arrPackage = $packages->toArray();

            foreach ($arrPackage as $k => $v) {
                if (in_array($v['id'], $packageId)) {
                    $arrStatus[$key]['status'][$v['id']] = 1;
                } else {
                    $arrStatus[$key]['status'][$v['id']] = 0;
                }
                if (in_array($v['id'], array_keys($packagesPrivilegesArr))) {
                    $arrStatus[$key]['rule'][$v['id']] = $packagesPrivilegesArr[$v['id']]['rule'];
                    $arrStatus[$key]['code'][$v['id']] = $packagesPrivilegesArr[$v['id']]['code'];
                } else {
                    $arrStatus[$key]['rule'][$v['id']] = '';
                    $arrStatus[$key]['code'][$v['id']] = '';
                }

            }
        }

        $data = [
            'packages'   => $packages,
            'privileges' => $privileges,
            'arrStatus'  => $arrStatus,
        ];
        $this->theme->set('vipactive', 'vipinfo');
        return $this->theme->scope('vipshop.vipinfo', $data)->render();
    }

    
    public function vipPayorder()
    {
        $vipcode = session('vipshopcode');

        $orderInfo = VipshopOrderModel::where('code', $vipcode)->firstOrFail();

        $userInfo = UserDetailModel::where('uid', Auth::id())->first();

        $payConfig = ConfigModel::getConfigByType('thirdpay');
        if($userInfo['balance']< $orderInfo['cash'] && empty($payConfig['alipay']['status']) && empty($payConfig['wechatpay']['status'])){
			return back()->with(['message' => '你的余额不足，请先充值']);
		}
        $data = [
            'order' => $orderInfo,
            'userInfo' => $userInfo,
            'payConfig' => $payConfig
        ];

        return $this->theme->scope('vipshop.vipPayorder', $data)->render();
    }

    
    public function postVipPayorder(PayOrderRequest $request)
    {
        $data = $request->except('_token');

        $userInfo = UserModel::find(Auth::id());

        $password = UserModel::encryptPassword($data['password'], $userInfo->salt);

        


        if ($password == $userInfo->alternate_password) {
            $vipcode = session('vipshopcode');
            $status = DB::transaction(function () use ($vipcode) {
                $orderInfo = VipshopOrderModel::where('code', $vipcode)->first();
                UserDetailModel::where('uid', Auth::id())->decrement('balance', $orderInfo->cash);
                FinancialModel::create([
                    'action' => 15,
                    'pay_type' => 1,
                    'cash' => $orderInfo->cash,
                    'uid' => Auth::id(),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                VipshopOrderModel::where('code', $orderInfo->code)->update(['status' => 1]);
                
                ShopPackageModel::where('shop_id',$orderInfo->shop_id)->where('uid',Auth::id())->where('status',0)->update([
                    'status' => 1,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $arrPrivilegeId = PackagePrivilegesModel::where('package_id', $orderInfo->package_id)->get(['privileges_id'])
                    ->map(function ($v, $k) {
                        return $v['privileges_id'];
                    });
                ShopPackageModel::create([
                    'shop_id' => $orderInfo->shop_id,
                    'package_id' => $orderInfo->package_id,
                    'privileges_package' => json_encode($arrPrivilegeId),
                    'uid' => Auth::id(),
                    'username' => Auth::User()->name,
                    'duration' => $orderInfo->time_period,
                    'price' => $orderInfo->cash,
                    'start_time' => date('Y-m-d H:i:s', time()),
                    'end_time' => date('Y-m-d H:i:s', strtotime('+' . $orderInfo->time_period . ' month')),
                    'status' => 0
                ]);

                $sysPrivilege = PrivilegesModel::getSystemPrivileges();

                $arr = array_intersect(array_keys($sysPrivilege),$arrPrivilegeId->toArray());
                $userMore = [];
                if($arr){
                    $packageConfig = PrivilegesModel::getPackageConfigById($orderInfo->package_id);
                    if(!empty($packageConfig)){
                        foreach($packageConfig as $k => $v){
                            if($v['code'] == 'TASK_WORK'){
                                $userMore['task_work'] = $v['rule'];
                            }else if($v['code'] == 'MOST_TASK_BOUNTY'){
                                $userMore['most_task_bounty'] = $v['rule'];
                            }else if($v['code'] == 'SKILL_TAGS_NUM'){
                                $userMore['skill_tags_num'] = $v['rule'];
                            }else if($v['code'] == 'SERVICE_OFF'){
                                $userMore['service_off'] = $v['rule'];
                            }
                        }
                    }

                }
                if(!empty($userMore)){
                    $userConfig = UserMoreModel::where('uid',Auth::id())->first();
                    if($userConfig){
                        $userMore['updated_at'] = date('Y-m-d H:i:s');
                        UserMoreModel::where('uid',Auth::id())->update($userMore);
                    }else{
                        $userMore['uid'] = Auth::id();
                        $userMore['created_at'] = date('Y-m-d H:i:s');
                        $userMore['updated_at'] = date('Y-m-d H:i:s');
                        UserMoreModel::create($userMore);
                    }
                    Cache::forget('user_more');
                }

            });

            if (is_null($status)) {
                return redirect('vipshop/vipsucceed');
            }
            return redirect('vipshop/vipfailure');
        }

        return back()->withErrors(['password' => '请输入正确的支付密码']);
    }

    
    public function thirdPayorder()
    {
        $type = Input::get('pay_type');

        $vipcode = session('vipshopcode');

        $vipOrder = VipshopOrderModel::where('code', $vipcode)->first();

        switch ($type) {
            case 'alipay':
                $config = ConfigModel::getPayConfig('alipay');
                $objOminipay = Omnipay::gateway('alipay');
                $objOminipay->setPartner($config['partner']);
                $objOminipay->setKey($config['key']);
                $objOminipay->setSellerEmail($config['sellerEmail']);
                $objOminipay->setReturnUrl(env('ALIPAY_RETURN_URL', url('/order/pay/alipay/return')));
                $objOminipay->setNotifyUrl(env('ALIPAY_NOTIFY_URL', url('/order/pay/alipay/notify')));
                $response = Omnipay::purchase([
                    'out_trade_no' => $vipOrder->code, 
                    'subject' => \CommonClass::getConfig('site_name') . '余额充值', 
                    'total_fee' => $vipOrder->cash, 
                ])->send();
                $response->redirect();
                break;
            case 'wechatpay':
                $config = ConfigModel::getPayConfig('wechatpay');
                $wechat = Omnipay::gateway('wechat');
                $wechat->setAppId($config['appId']);
                $wechat->setMchId($config['mchId']);
                $wechat->setAppKey($config['appKey']);
                $params = array(
                    'out_trade_no' => $vipOrder->code, 
                    'notify_url' => env('WECHAT_NOTIFY_URL', url('order/pay/wechat/notify')), 
                    'body' => \CommonClass::getConfig('site_name') . '余额充值', 
                    'total_fee' => $vipOrder->cash, 
                    'fee_type' => 'CNY', 
                );
                $response = $wechat->purchase($params)->send();

                $img = QrCode::size('280')->generate($response->getRedirectUrl());
                $view = array(
                    'cash' => $vipOrder->cash,
                    'img' => $img
                );
                $this->initTheme('userfinance');
                return $this->theme->scope('pay.wechatpay', $view)->render();
                break;
        }
    }

    
    public function vipSucceed()
    {
        return $this->theme->scope('vipshop.vipsucceed')->render();
    }

    
    public function vipFailure()
    {
        return $this->theme->scope('vipshop.vipfailure')->render();
    }

    
    public function feedback(Request $request){
        $data = $request->except('_token');
        $validator = Validator::make($data,[
            'desc' => 'required|max:255',
            'phone' => 'mobile_phone'
        ],
            [
                'desc.required' => '请输入投诉建议',
                'desc.max'      => '投诉建议字数超过限制',
                'phone.mobile_phone' => '请输入正确的手机格式'

            ]);
        $error = $validator->errors()->all();
        if(count($error)){
            return back()->with(['error'=>$validator->errors()->first()]);
        }
        $newdata = [
            'desc'          => $data['desc'],
            'created_time'  => date('Y-m-d h:i:s',time()),
            'phone'         => $data['phone'],
            'type'          => 1
        ];
        if($data['uid']){
            $newdata['uid'] = $data['uid'];
        }
        $res = FeedbackModel::create($newdata);
        if($res){
            return back()->with(['message'=>'投诉建议提交成功！']);
        }
        return back()->with(['error'=>'投诉建议提交失败！']);
    }
}