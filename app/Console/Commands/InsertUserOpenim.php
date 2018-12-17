<?php

namespace App\Console\Commands;

use App\Modules\Manage\Model\ConfigModel;
use App\Modules\Order\Model\ShopOrderModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use Illuminate\Console\Command;

class InsertUserOpenim extends Command
{
    
    protected $signature = 'InsertUserOpenim';

    
    protected $description = '导入用户数据到openim';

    
    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        
        $messageConfig = [];
        $config = ConfigModel::getConfigByAlias('app_message');
        if($config && !empty($config['rule'])){
            $messageConfig = json_decode($config['rule'],true);
        }
        $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();

        if(!empty($messageConfig)){

            
            $user = UserModel::select('id','name')->where('status',1)->get()->toArray();

            if(!empty($user)){
                foreach($user as $k => $v){
                    $username = strval($v['id']);
                    $userDetail = UserDetailModel::where('uid',$v['id'])->first(); $c = new \TopClient();
                    $c->appkey = isset($messageConfig['appkey']) ? $messageConfig['appkey'] : '';
                    $c->secretKey = isset($messageConfig['secretKey']) ? $messageConfig['secretKey'] : '';

                    
                    $req = new \OpenimUsersGetRequest();
                    $req->setUserids($username);
                    $userInfos = $c->execute($req);
                    if(!isset($userInfos->userinfos->userinfos)){
                        
                        $req = new \OpenimUsersAddRequest();
                        $userinfos = new \Userinfos();
                        $userinfos->nick     = $v['name'];
                        $userinfos->icon_url = $userDetail['avatar']?$domain->rule.'/'.$userDetail['avatar']:$userDetail['avatar'];
                        $userinfos->email    = $userDetail['email'];
                        $userinfos->mobile   = $userDetail['mobile'];
                        $userinfos->userid   = $v['id'];
                        $userinfos->password = '';
                        $userinfos->name     = $userDetail['name'];
                        $req->setUserinfos(json_encode($userinfos));
                        $c->execute($req);
                    }else{
                        
                        $req = new \OpenimUsersUpdateRequest();
                        $userinfos = new \Userinfos();
                         $userinfos->nick        = $v['name'];
                        $userinfos->icon_url = $userDetail['avatar']?$domain->rule.'/'.$userDetail['avatar']:$userDetail['avatar'];
                        $userinfos->email       = $userDetail['email'];
                        $userinfos->mobile     = $userDetail['mobile'];
                        $userinfos->userid     = $v['id'];
                        $userinfos->name         = $v['name'];
                        $req->setUserinfos(json_encode($userinfos));
                        $c->execute($req);
                    }
                }
            }

        }

    }



}
