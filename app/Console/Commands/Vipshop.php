<?php

namespace App\Console\Commands;

use App\Modules\User\Model\UserModel;
use App\Modules\User\Model\UserMoreModel;
use App\Modules\Vipshop\Models\PackageModel;
use App\Modules\Vipshop\Models\PrivilegesModel;
use App\Modules\Vipshop\Models\ShopPackageModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class Vipshop extends Command
{

    protected $signature = 'vipshop';


    protected $description = 'Command description';


    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {

        $systemPackage = PrivilegesModel::getSystemPackage();

        $now = date('Y-m-d H:i:s', time());


        $shopPackageOver = ShopPackageModel::where('end_time','<',$now)->where('status',0)->get()->toArray();

        if($shopPackageOver){
            foreach($shopPackageOver as $k => $v){
                $userConfig = UserMoreModel::where('uid',$v['uid'])->first();
                if($systemPackage){
                    $userMore['task_work'] = in_array('TASK_WORK',array_keys($systemPackage)) ? $systemPackage['TASK_WORK']['rule'] : 0;
                    $userMore['most_task_bounty'] = in_array('MOST_TASK_BOUNTY',array_keys($systemPackage)) ? $systemPackage['MOST_TASK_BOUNTY']['rule'] : 0;
                    $userMore['skill_tags_num'] = in_array('SKILL_TAGS_NUM',array_keys($systemPackage)) ? $systemPackage['SKILL_TAGS_NUM']['rule'] : 0;
                    $userMore['service_off'] = in_array('SERVICE_OFF',array_keys($systemPackage)) ? $systemPackage['SERVICE_OFF']['rule'] : 0;
                }else{
                    $userMore['task_work'] = 0;
                    $userMore['most_task_bounty'] =  0;
                    $userMore['skill_tags_num'] =  0;
                    $userMore['service_off'] = 0;
                }
                if($userConfig){
                    $userMore['updated_at'] = date('Y-m-d H:i:s');
                    UserMoreModel::where('uid',$v['uid'])->update($userMore);
                }else{
                    $userMore['uid'] = $v['uid'];
                    $userMore['created_at'] = date('Y-m-d H:i:s');
                    $userMore['updated_at'] = date('Y-m-d H:i:s');
                    UserMoreModel::create($userMore);
                }
            }
            Cache::forget('user_more');
        }

        ShopPackageModel::where('end_time','<',$now)->update(['status' => 1]);


        $sysPach = PackageModel::where('type',1)->where('type_status',1)->first();
        $user = UserModel::get()->toArray();
        if($user){
            foreach($user as $k => $v){
                $userConfig = UserMoreModel::where('uid',$v['id'])->first();
                if(!$userConfig){

                    $vip = ShopPackageModel::where('uid',$v['id'])->where('status',0)->orderBy('id','desc')->first();
                    if($vip){

                        $packageArr = PrivilegesModel::getPackageConfigById($vip->package_id);
                        $userMore['task_work'] = in_array('TASK_WORK',array_keys($packageArr)) ? $packageArr['TASK_WORK']['rule'] : 0;
                        $userMore['most_task_bounty'] = in_array('MOST_TASK_BOUNTY',array_keys($packageArr)) ? $packageArr['MOST_TASK_BOUNTY']['rule'] : 0;
                        $userMore['skill_tags_num'] = in_array('SKILL_TAGS_NUM',array_keys($packageArr)) ? $packageArr['SKILL_TAGS_NUM']['rule'] : 0;
                        $userMore['service_off'] = in_array('SERVICE_OFF',array_keys($packageArr)) ? $packageArr['SERVICE_OFF']['rule'] : 0;
                    }else{
                        if($systemPackage){
                            $userMore['task_work'] = in_array('TASK_WORK',array_keys($systemPackage)) ? $systemPackage['TASK_WORK']['rule'] : 0;
                            $userMore['most_task_bounty'] = in_array('MOST_TASK_BOUNTY',array_keys($systemPackage)) ? $systemPackage['MOST_TASK_BOUNTY']['rule'] : 0;
                            $userMore['skill_tags_num'] = in_array('SKILL_TAGS_NUM',array_keys($systemPackage)) ? $systemPackage['SKILL_TAGS_NUM']['rule'] : 0;
                            $userMore['service_off'] = in_array('SERVICE_OFF',array_keys($systemPackage)) ? $systemPackage['SERVICE_OFF']['rule'] : 0;

                        }else{
                            $userMore['task_work'] = 0;
                            $userMore['most_task_bounty'] =  0;
                            $userMore['skill_tags_num'] =  0;
                            $userMore['service_off'] = 0;
                        }
                    }
                    $userMore['uid'] = $v['id'];
                    $userMore['created_at'] = date('Y-m-d H:i:s');
                    $userMore['updated_at'] = date('Y-m-d H:i:s');
                    UserMoreModel::create($userMore);
                }else{
                    if($sysPach){
                        $vip = ShopPackageModel::where('uid',$v['id'])->where('status',0)->orderBy('id','desc')->first();
                        if(!$vip){
                            $userMore['task_work'] = in_array('TASK_WORK',array_keys($systemPackage)) ? $systemPackage['TASK_WORK']['rule'] : 0;
                            $userMore['most_task_bounty'] = in_array('MOST_TASK_BOUNTY',array_keys($systemPackage)) ? $systemPackage['MOST_TASK_BOUNTY']['rule'] : 0;
                            $userMore['skill_tags_num'] = in_array('SKILL_TAGS_NUM',array_keys($systemPackage)) ? $systemPackage['SKILL_TAGS_NUM']['rule'] : 0;
                            $userMore['service_off'] = in_array('SERVICE_OFF',array_keys($systemPackage)) ? $systemPackage['SERVICE_OFF']['rule'] : 0;
                            $userMore['updated_at'] = date('Y-m-d H:i:s');
                            UserMoreModel::where('uid',$v['id'])->update($userMore);
                        }
                    }

                }

            }
        }
        if($sysPach){
            PackageModel::where('type',1)->where('type_status',1)->update([
                'type_status' => 0
            ]);
        }

    }
}
