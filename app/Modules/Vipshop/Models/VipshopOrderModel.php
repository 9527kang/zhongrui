<?php

namespace App\Modules\Vipshop\Models;

use App\Modules\Finance\Model\FinancialModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use App\Modules\User\Model\UserMoreModel;
use Illuminate\Database\Eloquent\Model;
use DB;
use Cache;

class VipshopOrderModel extends Model
{
    protected $table = 'vipshop_order';

    protected $fillable = [
        'code', 'title', 'uid', 'package_id', 'shop_id', 'cash', 'time_period', 'status'
    ];

    
    static public function payVipShop($data,$payType=1)
    {
        $status = DB::transaction(function () use ($data,$payType) {
            $orderInfo = VipshopOrderModel::where('code', $data['code'])->first();
            
            FinancialModel::create([
                'action' => 15,
                'pay_type' => $payType,
                'cash' => $orderInfo->cash,
                'uid' => $orderInfo['uid'],
                'pay_account' => $data['pay_account'],
                'pay_code' => $data['pay_code'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
            VipshopOrderModel::where('code', $orderInfo->code)->update(['status' => 1]);
            
            ShopPackageModel::where('shop_id',$orderInfo->shop_id)->where('uid',$orderInfo['uid'])->where('status',0)->update([
                'status' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $arrPrivilegeId = PackagePrivilegesModel::where('package_id', $orderInfo->package_id)->get(['privileges_id'])
                ->map(function ($v, $k) {
                    return $v['privileges_id'];
                });
            $user = UserModel::find($orderInfo['uid']);
            ShopPackageModel::create([
                'shop_id' => $orderInfo->shop_id,
                'package_id' => $orderInfo->package_id,
                'privileges_package' => json_encode($arrPrivilegeId),
                'uid' => $orderInfo['uid'],
                'username' => $user->name,
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
                $userConfig = UserMoreModel::where('uid',$orderInfo['uid'])->first();
                if($userConfig){
                    $userMore['updated_at'] = date('Y-m-d H:i:s');
                    UserMoreModel::where('uid',Auth::id())->update($userMore);
                }else{
                    $userMore['uid'] = $orderInfo['uid'];
                    $userMore['created_at'] = date('Y-m-d H:i:s');
                    $userMore['updated_at'] = date('Y-m-d H:i:s');
                    UserMoreModel::create($userMore);
                }
                Cache::forget('user_more');
            }
        });
        return $status;
    }
}
