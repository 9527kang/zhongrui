<?php

namespace App\Modules\User\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use Cache;

class UserMoreModel extends Model
{
    
    protected $table = 'user_more';

    protected $fillable = [
        'id',
        'uid',
        'task_work',
        'most_task_bounty',
        'skill_tags_num',
        'service_off',
        'created_at','updated_at'
    ];

    
    static public function getUserMoreByUid($uid)
    {
        $userMore = self::getAllUserMore();
        $userMoreConfig = [];
        if(in_array($uid,array_keys($userMore))){
            $userMoreConfig = $userMore[$uid];
        }
        return $userMoreConfig;
    }

    
    static public function getAllUserMore()
    {
        if(Cache::has('user_more')){
            $userMore = Cache::get('user_more');
        }else{
            $userMore = self::get()->toArray();
            $userMore = \CommonClass::setArrayKey($userMore,'uid');
            Cache::put('user_more',$userMore,60*24);
        }
        return $userMore;
    }
}
