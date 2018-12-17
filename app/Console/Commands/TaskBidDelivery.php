<?php

namespace App\Console\Commands;

use App\Modules\Finance\Model\FinancialModel;
use App\Modules\Task\Model\TaskPaySectionModel;
use App\Modules\Task\Model\TaskPayTypeModel;
use App\Modules\Task\Model\TaskTypeModel;
use App\Modules\Task\Model\WorkModel;
use App\Modules\User\Model\TaskModel;
use App\Modules\User\Model\UserDetailModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TaskBidDelivery extends Command
{
    
    protected $signature = 'taskBidDelivery';

    
    protected $description = '招标任务交付超时';

    
    public function __construct()
    {
        parent::__construct();
    }

    
    public function handle()
    {
        
        $taskTypeId = TaskTypeModel::getTaskTypeIdByAlias('zhaobiao');
        
        $task = TaskModel::where('type_id',$taskTypeId)->where('status',7)->get()->toArray();
        
        $filled_tasks = self::filledTasks($task);
        
        if(count($filled_tasks)!=0){
            foreach($filled_tasks as $v){
                DB::transaction(function() use($v){
                    
                    TaskModel::where('id',$v['id'])->update(['status'=>10,'end_at'=>date('Y-m-d H:i:s',time())]);
                    
                    
                    $task_fail_percentage = TaskModel::where('id',$v['id'])->first();
                    $task_fail_percentage = $task_fail_percentage['task_fail_draw_ratio'];
                    if($task_fail_percentage!=0){
                        $balance = $v['bounty']*(1-$task_fail_percentage/100);
                    }else{
                        $balance = $v['bounty'];
                    }
                    UserDetailModel::where('uid',$v['uid'])->increment('balance',$balance);
                    
                    $finance_data = [
                        'action'=>7,
                        'pay_type'=>1,
                        'cash'=>$balance,
                        'uid'=>$v['uid'],
                        'created_at'=>date('Y-m-d H:i:s',time()),
                        'updated_at'=>date('Y-m-d H:i:s',time()),
                    ];
                    FinancialModel::create($finance_data);
                });
            }
        }
        $successed_tasks = self::filledTasks($task,2);
        if(!empty($successed_tasks)){
            
            $onwer_expired = self::expireTaskOwner($successed_tasks);
            $onwer_expired = array_flatten($onwer_expired);
            if($onwer_expired){
                foreach($onwer_expired as $v){
                    $work_data = WorkModel::where('id',$v)->first();
                    $data['task_id'] = $work_data['task_id'];
                    $data['uid'] = $work_data['uid'];
                    $data['work_id'] = $v;

                    
                    $paySectionInfo = [
                        'verify_status' => 2,
                        'section_status' => 1,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                    TaskPaySectionModel::where('task_id',$data['task_id'])
                        ->where('work_id',$data['work_id'])->update($paySectionInfo);
                    
                    WorkModel::where('id', $data['work_id'])->update(['status' => 5]);

                    
                    $money = TaskPaySectionModel::where('task_id',$data['task_id'])->whereIn('section_status',[0,1])->sum('price');

                    
                    
                    $taskInfo = TaskModel::where('id',$data['task_id'])->first();
                    $task_fail_percentage = $taskInfo['task_fail_draw_ratio'];
                    if($task_fail_percentage!=0){
                        $balance = $money*(1-$task_fail_percentage/100);
                    }else{
                        $balance = $money;
                    }
                    UserDetailModel::where('uid',$taskInfo['uid'])->increment('balance',$balance);
                    
                    $finance_data = [
                        'action'=>7,
                        'pay_type'=>1,
                        'cash'=>$balance,
                        'uid'=>$taskInfo['uid'],
                        'created_at'=>date('Y-m-d H:i:s',time()),
                        'updated_at'=>date('Y-m-d H:i:s',time()),
                    ];
                    FinancialModel::create($finance_data);
                    
                    TaskModel::where('id',$data['task_id'])->update(['status'=>10,'end_at'=>date('Y-m-d H:i:s',time())]);
                }
            }else{
                $work_expired = self::expireTaskOwner($successed_tasks,2);
                $work_expired = array_flatten($work_expired);
                if($work_expired){
                    foreach($work_expired as $k => $v){
                        $work_data = WorkModel::where('id',$v)->first();
                        
                        $money = TaskPaySectionModel::where('task_id',$work_data['task_id'])->whereIn('section_status',[0,1])->sum('price');

                        
                        
                        $taskInfo = TaskModel::where('id',$work_data['task_id'])->first();
                        $task_fail_percentage = $taskInfo['task_fail_draw_ratio'];
                        if($task_fail_percentage!=0){
                            $balance = $money*(1-$task_fail_percentage/100);
                        }else{
                            $balance = $money;
                        }
                        UserDetailModel::where('uid',$taskInfo['uid'])->increment('balance',$balance);
                        
                        $finance_data = [
                            'action'=>7,
                            'pay_type'=>1,
                            'cash'=>$balance,
                            'uid'=>$taskInfo['uid'],
                            'created_at'=>date('Y-m-d H:i:s',time()),
                            'updated_at'=>date('Y-m-d H:i:s',time()),
                        ];
                        FinancialModel::create($finance_data);
                        
                        TaskModel::where('id',$work_data['task_id'])->update(['status'=>10,'end_at'=>date('Y-m-d H:i:s',time())]);
                    }
                }
            }


        }


    }

    private function expireTaskOwner($data,$type=1)
    {
        $task_check_time_limit = \CommonClass::getConfig('bid_check_time_limit');
        $task_check_time_limit = $task_check_time_limit*24*3600;
        $expired_works = [];
        $passWorks = [];
        foreach($data as $v)
        {
            
            $works = WorkModel::where('task_id',$v['id'])->where('status',2)->get()->toArray();
            $works_expired = [];
            if(!empty($works)){
                foreach($works as $v) {
                    if((strtotime($v['created_at']) + $task_check_time_limit)<=time()){
                        $works_expired[] = $v['id'];
                    }
                }
            }

            
            $works_delivery = WorkModel::where('task_id',$v['id'])->where('status','>',2)->lists('id')->toArray();
            $works_diff = array_diff($works_expired,$works_delivery);
            if(count($works_diff)>0)
            {
                $expired_works[] = $works_diff;
            }
            if($type == 2){
                $passWorksObj = WorkModel::where('task_id',$v['id'])->where('status',3)->orderBy('id','desc')->first();
                if($passWorksObj){
                    $passWorks[] = $passWorksObj->id;
                }
            }
        }
        if($type == 1){
            return $expired_works;
        }else{
            return $passWorks;
        }

    }

    
    private function filledTasks($data,$type=1)
    {
        $task_delivery_max_time = \CommonClass::getConfig('bid_delivery_max_time');
        
        $task_delivery_max_time = $task_delivery_max_time*24*3600;
        $filled = [];
        $successed = [];
        foreach($data as $k=>$v)
        {
            
            $taskPayType = TaskPayTypeModel::where('task_id',$v['id'])
                ->where('status',1)->first();
            if(!empty($taskPayType)){
                if((strtotime($v['checked_at'])+$task_delivery_max_time)<=time())
                {

                    
                    $work = WorkModel::where('task_id', $v['id'])->whereIn('status',[2,3,4,5])->count();
                    if ($work == 0) {
                        $filled[] = $v;
                    } else {
                        $successed[] = $v;
                    }
                }
            }else{
                if((strtotime($v['selected_work_at'])+$task_delivery_max_time)<=time())
                {
                    $filled[] = $v;
                }
            }

        }
        if($type==1){
            return $filled;
        }else{
            return $successed;
        }
    }
}
