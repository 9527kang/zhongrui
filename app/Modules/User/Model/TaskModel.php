<?php

namespace App\Modules\User\Model;

use App\Modules\Task\Model\WorkModel;
use Illuminate\Database\Eloquent\Model;

class TaskModel extends Model
{

    
    protected $table = 'task';


    
    static public function getNewTask($num)
    {
        $task = TaskModel::where('task.status','>',2)
            ->where(function($query){
                $query->where(function($querys){
                    $querys->where('task.bounty_status',1)
                        ->where('task_type.alias','xuanshang');
                })->orwhere(function($querys){
                    $querys->where('task_type.alias','zhaobiao');
                });
            })
            ->where('task.begin_at','<',date('Y-m-d H:i:s',time()))
            ->where('task.status','!=',10)
            ->join('users','users.id','=','task.uid')
            ->leftJoin('task_type','task.type_id','=','task_type.id')
            ->leftJoin('user_detail','user_detail.uid','=','task.uid')
            ->select('task.*','users.name','user_detail.avatar')
            ->orderBy('task.top_status','DESC')
            ->orderBy('task.created_at','DESC')
            ->limit($num)->get()->toArray();
        return $task;
    }

    static public function getNewWorkBid($num)
    {
        $active = WorkModel::where('work.status',1)->join('users','users.id','=','work.uid')
            ->leftJoin('task','task.id','=','work.task_id')
            ->leftJoin('user_detail','user_detail.uid','=','work.uid')
            ->select('work.*','users.name','task.show_cash','task.title','user_detail.avatar')
            ->orderBy('work.bid_at','Desc')->limit($num)->get()->toArray();
        return $active;
    }
}