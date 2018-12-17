<?php

namespace App\Modules\Vipshop\Models;

use App\Modules\Shop\Models\ShopModel;
use App\Modules\User\Model\UserModel;
use Illuminate\Database\Eloquent\Model;

class InterviewModel extends Model
{
    
    protected $table = 'interview';

    protected $primaryKey = 'id';

    protected $fillable = [

        'title', 'uid', 'username', 'shop_id', 'shop_name', 'shop_cover', 'desc','list','created_at','updated_at','view_count'

    ];

    
    static function interviewList($data){
        $interviewList = InterviewModel::select('*');
        if(isset($data['username'])){
            $interviewList = $interviewList->where('username','like','%'.$data['username'].'%');
        }
        if(isset($data['shop_name'])){
            $interviewList = $interviewList->where('shop_name','like','%'.$data['shop_name'].'%');
        }
        $interviewList = $interviewList->orderBy('list','asc')->orderBy('created_at','desc')->paginate(10);
        return $interviewList;
    }


    
    static function deleteInterview($id){
        $interviewInfo = InterviewModel::find(intval($id));
        if(empty($interviewInfo)){
            return 2;
        }
        $res = $interviewInfo->delete();
        return $res?1:0;                  
    }


    
    static function interviewShop(){
        $shopInfo = ShopPackageModel::join('shop','shop_package.shop_id','=','shop.id')
            ->where('shop_package.status',0)
            ->where('shop.status',1)
            ->select('shop.id','shop.shop_name')
            ->groupBy('shop_package.shop_id')
            ->get()->toArray();
        return $shopInfo;
    }


    
    static function shopInfo($id){
        $shopUser = [];
        $shopId = intval($id);
        $shopInfo = ShopModel::where(['id' => $shopId])->first();
        if(empty($shopInfo)){
            return false;                           
        }
        $userInfo = UserModel::where('id',$shopInfo->uid)->select('name')->first();
        if(empty($userInfo)){
            return false;                         
        }
        
        $shopUser['shop_name'] = $shopInfo->shop_name;
        $shopUser['shop_cover'] = $shopInfo->shop_pic;
        $shopUser['uid'] = $shopInfo->uid;
        $shopUser['username'] = $userInfo->name;
        return $shopUser;
    }

    
    static function addInterview(array $data){
        $interviewInfo = [
            'title' => $data['title'],
            'uid' => $data['uid'],
            'username' => $data['username'],
            'shop_id' => $data['shop_id'],
            'shop_name' => $data['shop_name'],
            'shop_cover' => $data['shop_cover'],
            'desc' => $data['desc'],
            'list' => $data['list'],
            'created_at' => date('Y-m-d H:i:s',time()),
            'updated_at' => date('Y-m-d H:i:s',time())
        ];
        $res = InterviewModel::create($interviewInfo);
        return $res?true:false;                 

    }

    
    static function interviewDetail($id){
        $interviewInfo = InterviewModel::find(intval($id));
        if(empty($interviewInfo)){
            return false;
        }
        return $interviewInfo;
    }


    
    static function updateInterview($id,array $data){
        $interviewInfo = [
            'title' => $data['title'],
            'uid' => $data['uid'],
            'username' => $data['username'],
            'shop_id' => $data['shop_id'],
            'shop_name' => $data['shop_name'],
            'shop_cover' => $data['shop_cover'],
            'desc' => $data['desc'],
            'list' => $data['list'],
            'updated_at' => date('Y-m-d H:i:s',time())
        ];
        $res = InterviewModel::where('id',intval($id))->update($interviewInfo);
        return $res?true:false;                 

    }

}
