<?php

namespace App\Modules\Task\Model;

use App\Modules\Employ\Models\EmployUserModel;
use App\Modules\Finance\Model\FinancialModel;
use App\Modules\Manage\Model\ConfigModel;
use App\Modules\Manage\Model\MessageTemplateModel;
use App\Modules\Order\Model\OrderModel;
use App\Modules\User\Model\AttachmentModel;
use App\Modules\User\Model\MessageReceiveModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use App\Modules\Task\Model\TaskCateModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Log;





class TaskModel extends Model
{
    protected $table = 'task';
    protected $fillable = [
        'title', 'desc', 'type_id', 'cate_id', 'phone', 'region_limit', 'status', 'bounty', 'bounty_status', 'created_at', 'updated_at',
        'verified_at', 'begin_at', 'end_at', 'delivery_deadline', 'show_cash', 'real_cash', 'deposit_cash', 'province', 'city', 'area',
        'view_count', 'delivery_count', 'uid', 'username', 'worker_num', 'selected_work_at', 'publicity_at', 'checked_at', 'comment_at',
        'top_status', 'task_success_draw_ratio', 'task_fail_draw_ratio', 'engine_status', 'work_status','kee_status','url','web_site','type','tag_name','tag_pname'
    ];
    public function province()
    {
        return $this->hasOne('App\Modules\User\Model\DistrictModel','id','province');
    }
    public function city()
    {
        return $this->hasOne('App\Modules\User\Model\DistrictModel','id','city');
    }
    static public function myTasks($data)
    {
        $query = self::select('task.*', 'tt.name as type_name','tt.alias','us.name as nickname', 'ud.avatar', 'tc.name as cate_name', 'province.name as province_name', 'city.name as city_name')
            ->where('task.status', '>', 0)
            ->where('task.status', '<=', 11)->where('task.uid', $data['uid'])->where(function($query){
				$query->where(function($querys){
					 $querys->where('task.bounty_status',1)->where('tt.alias','xuanshang');
				 })->orwhere(function($querys){
					 $querys->whereIn('task.bounty_status',[0,1])->where('tt.alias','zhaobiao');
				 });
			});
        
        if (isset($data['status']) && $data['status'] != 0) {
            

			switch($data['status']){
				case 1:
                    $status = [6];
                    break;
                case 2:
                    $status = [4,5];
                    break;
                case 3:
                    $status = [7];
                    break;
                case 4:
                    $status = [8, 9, 10];
                    break;
                case 5:
                    $status = [2, 11];
                    break;
				case 6:
					$status = [1];
					break;
				case 7:
					$status = [3,4];
					break;
				case 8:
					$status = [5];
					break;
                case 9:
					$status = [6];
					break;
 				case 10:
					$status = [7];
					break;
                case 11:
					$status = [11];
					break;
                case 12:
					$status = [8,9];
					break;
                case 13:
					$status = [10];
					break; 
                case 14:
					$status = [8,9,10];
					break;
                case 15:
                    $status = [3];
                    break;
			}
            $query->whereIn('task.status', $status);
        }
        
        if (isset($data['time'])) {
            switch ($data['time']) {
                case 1:
                    $query->whereBetween('task.created_at', [date('Y-m-d H:i:s', strtotime('-1 month')), date('Y-m-d H:i:s', time())]);
                    break;
                case 2:
                    $query->whereBetween('task.created_at', [date('Y-m-d H:i:s', strtotime('-3 month')), date('Y-m-d H:i:s', time())]);
                    break;
                case 3:
                    $query->whereBetween('task.created_at', [date('Y-m-d H:i:s', strtotime('-6 month')), date('Y-m-d H:i:s', time())]);
                    break;
            }

        }
        
		if(isset($data['type'])){
			$query->where('type_id',$data['type']);
		}
        $data = $query->join('task_type as tt', 'task.type_id', '=', 'tt.id')
            ->leftjoin('district as province', 'province.id', '=', 'task.province')
            ->leftjoin('district as city', 'city.id', '=', 'task.city')
            ->leftjoin('users as us', 'us.id', '=', 'task.uid')
            ->leftjoin('user_detail as ud', 'ud.uid', '=', 'task.uid')
            ->leftjoin('cate as tc', 'tc.id', '=', 'task.cate_id')
            ->orderBy('task.created_at', 'desc')
            ->paginate(5);
        return $data;
    }
    
    public static function findBy($data,$paginate=10)
    {
        $query = self::select('task.*', 'b.name as type_name', 'b.alias as type_alias', 'us.name as user_name')->where('task.status', '>', 2)
            ->where(function($query){
				 $query->where(function($querys){
					 $querys->where('task.bounty_status',1)->where('b.alias','xuanshang');
				 })->orwhere(function($querys){
					 $querys->whereIn('task.bounty_status',[0,1])->where('b.alias','zhaobiao');
				 });
			})
			->where('task.status', '<=', 9)->where('begin_at', "<=", date('Y-m-d H:i:s', time()))
            ->orderBy('task.top_status', 'desc');
        
        if (isset($data['keywords'])) {
            $query = $query->where('task.title', 'like', '%' . e($data['keywords']) . '%');
        }
		
		if(isset($data['taskType']) && $data['taskType']!=0){
			$query->where('task.type_id', $data['taskType']);
		}
        
        if (isset($data['category']) && $data['category'] != 0) {
            
            $category_ids = TaskCateModel::findCateIds($data['category']);
            $query->whereIn('task.cate_id', $category_ids);
        }
        
        if (isset($data['province'])) {
            $query->where('task.province', intval($data['province']));
        }
        if (isset($data['city'])) {
            $query->where('task.city', intval($data['city']));
        }
        if (isset($data['area'])) {
            $query->where('task.area', intval($data['area']));
        }
        
        if (isset($data['status'])) {
            switch ($data['status']) {
                case 1:
                    
					$status=[3, 4, 6];
                    break;
                case 2:
                    $status = [5];
                    break;
                case 3:
                    $status = [6, 7];
                    break;
                case 4:
                    $status = [8, 9];
                    break;
				case 12:
				   $status = [8, 9,10];
                   break;
            }
            $query->whereIn('task.status', $status);
        }
        
        if (isset($data['desc']) && $data['desc'] != 'created_at') {
            $query->orderBy('task.'.$data['desc'], 'desc');
        } elseif (isset($data['desc']) && $data['desc'] == 'created_at') {
            $query->orderBy('task.created_at');
        } else {
            $query->orderBy('task.created_at', 'desc');
        }
        $data = $query->join('task_type as b', 'task.type_id', '=', 'b.id')
            ->leftjoin('users as us', 'us.id', '=', 'task.uid')
            ->paginate($paginate);
        return $data;
    }

    
    static function findByCity($data, $city)
    {
        $query = self::select('task.*', 'b.name as type_name', 'us.name as user_name')->where('task.status', '>', 2)
            
            ->where('task.status', '<=', 9)->where('begin_at', "<=", date('Y-m-d H:i:s', time()))
            ->where('task.region_limit', 1)
            ->orderBy('top_status', 'desc');
        
        if (isset($data['keywords'])) {
            $query = $query->where('task.title', 'like', '%' . e($data['keywords']) . '%');
        }
        
        if (isset($data['category']) && $data['category'] != 0) {
            
            $category_ids = TaskCateModel::findCateIds($data['category']);
            $query->whereIn('cate_id', $category_ids);
        }
        
        if (isset($city)) {
            $query->where(function ($query) use ($city) {
                $query->where('province', $city)->orwhere('city', $city);
            });
        }

        if (isset($data['area'])) {
            $query->where(function ($query) use ($data) {
                $query->where('city', $data['area'])->orwhere('area', $data['area']);
            });
        }
        
        if (isset($data['status'])) {
            switch ($data['status']) {
                case 1:
                    $status = [4];
                    break;
                case 2:
                    $status = [5];
                    break;
                case 3:
                    $status = [6, 7];
                    break;
                case 4:
                    $status = [8, 9];
                    break;
            }
            $query->whereIn('task.status', $status);
        }
        
        if (isset($data['desc']) && $data['desc'] != 'created_at') {
            $query->orderBy($data['desc'], 'desc');
        } elseif (isset($data['desc']) && $data['desc'] == 'created_at') {
            $query->orderBy('created_at');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $data = $query->join('task_type as b', 'task.type_id', '=', 'b.id')
            ->leftjoin('users as us', 'us.id', '=', 'task.uid')
            ->paginate(10);

        return $data;
    }

    
    static public function createTask($data)
    {
        $status = DB::transaction(function () use ($data) {
            $taskTypeAlias = 'xuanshang';
            $taskType = TaskTypeModel::find($data['type_id']);
            if(!empty($taskType)){
                $taskTypeAlias = $taskType['alias'];
            }
			if(isset($data['task_id'])){
                self::where("id",$data['task_id'])->update([
                     'phone'=>$data['phone'],
                     'cate_id'=>$data['cate_id'],
                     'province'=>$data['province'],
                     'city'=>$data['city'],
                     'area'=>$data['area'],
                     'title'=>$data['title'],
                     'bounty'=>$data['bounty'],
                     'worker_num'=>$data['worker_num'],
                     'type_id'=>$data['type_id'],
                     'begin_at'=>$data['begin_at'],
                     'delivery_deadline'=>$data['delivery_deadline'],
                     'desc'=>$data['desc'],
                     'created_at'=>$data['created_at'],
                     'show_cash'=>$data['show_cash'],
                     'status'=>$data['status'],
                     'task_success_draw_ratio'=>$data['task_success_draw_ratio'],
                     'task_fail_draw_ratio'=>$data['task_fail_draw_ratio'],
                     'kee_status' => $data['kee_status']
			   ]);
			    $result['id']=$data['task_id'];
			}else{
				$result = self::create($data);
			}
            if (!empty($data['file_id'])) {
                
                $file_able_ids = AttachmentModel::fileAble($data['file_id']);
                $file_able_ids = array_flatten($file_able_ids);
                if(isset($data['task_id'])){
					TaskAttachmentModel::where('task_id',$data['task_id'])->delete();
				}
                foreach ($file_able_ids as $v) {
                    $attachment_data = [
                        'task_id' => $result['id'],
                        'attachment_id' => $v,
                        'created_at' => date('Y-m-d H:i:s', time()),
                    ];

                    TaskAttachmentModel::create($attachment_data);
                }
                
                $attachmentModel = new AttachmentModel();
                $attachmentModel->statusChange($file_able_ids);
            }

            if (!empty($data['product'])) {
                if(isset($data['task_id'])){
                    TaskServiceModel::where('task_id',$data['task_id'])->delete();
                }
                foreach ($data['product'] as $k => $v) {
                    $service_data = [
                        'task_id' => $result['id'],
                        'service_id' => $v,
                        'created_at' => date('Y-m-d H:i:s', time()),
                    ];

                    TaskServiceModel::create($service_data);
					                 
                }
            }

            switch($taskTypeAlias){
                case 'xuanshang':
                    break;
                case 'zhaobiao':
                    
                    UserDetailModel::where('uid', $data['uid'])->increment('publish_task_num', 1);
                    
                    
                    $task_publish_success = MessageTemplateModel::where('code_name', 'task_publish_success')->where('is_open', 1)->first();
                    if ($task_publish_success) {
                        $task = self::where('id', $result['id'])->first()->toArray();
                        $task_status = [
                            'status' => [
                                0 => '暂不发布',
                                1 => '已经发布',
                                2 => '赏金托管',
                                3 => '审核通过',
                                4 => '威客交稿',
                                5 => '雇主选稿',
                                6 => '任务公示',
                                7 => '交付验收',
                                8 => '双方互评'
                            ]
                        ];
                        $task = \CommonClass::intToString([$task], $task_status);
                        $task = $task[0];
                        $user = UserModel::where('id', $data['uid'])->first();
                        $site_name = \CommonClass::getConfig('site_name');
                        $domain = \CommonClass::getDomain();
                        
                        
                        $messageVariableArr = [
                            'username' => $user['name'],
                            'task_number' => $task['id'],
                            'task_title' => $task['title'],
                            'task_status' => $task['status_text'],
                            'website' => $site_name,
                            'href' => $domain . '/task/' . $task['id'],
                            'task_link' => $task['title'],
                            'start_time' => $task['begin_at'],
                            'manuscript_end_time' => $task['delivery_deadline'],
                        ];
                        if($task_publish_success->is_on_site == 1){
                            \MessageTemplateClass::getMeaasgeByCode('task_publish_success',$user['id'],2,$messageVariableArr,$task_publish_success['name']);
                        }
                        
                        if($task_publish_success->is_send_email == 1){
                            $email = $user->email;
                            \MessageTemplateClass::sendEmailByCode('task_publish_success',$email,$messageVariableArr,$task_publish_success['name']);
                        }
                        if($task_publish_success->is_send_mobile == 1 && $task_publish_success->code_mobile && $user->mobile){
                            $scheme = ConfigModel::phpSmsConfig('phpsms_scheme');
                            $templates = [
                                $scheme => $task_publish_success->code_mobile,
                            ];
                            $messageVariableArr = [
                                'username' => $user['name'],
                                'task_number' => $task['id'],
                                'task_title' => $task['title'],
                                'task_status' => $task['status_text'],
                                'website' => $site_name,
                                'task_link' => $task['title'],
                                'start_time' => $task['begin_at'],
                                'manuscript_end_time' => $task['delivery_deadline'],
                            ];
                            \SmsClass::sendSms($user->mobile, $templates, $messageVariableArr);
                        }
                    }

                    break;
            }
            return $result;
        });
        return $status;
    }


    
    static function findById($id)
    {
        $data = self::select('task.*', 'b.name as cate_name', 'c.name as type_name','c.alias as task_type')
            ->where('task.id', '=', $id)
            ->leftjoin('cate as b', 'task.cate_id', '=', 'b.id')
            ->leftjoin('task_type as c', 'task.type_id', '=', 'c.id')
            ->first();

        return $data;
    }

    
    public function taskMoney($id,$off=100)
    {
        $bounty = self::select('task.bounty')->where('id', '=', $id)->first();
        $bounty = $bounty['bounty'];
        $service = TaskServiceModel::select('task_service.service_id')
            ->where('task_id', '=', $id)->get()->toArray();
        $service = array_flatten($service);
        $serviceModel = new ServiceModel();
        $service_money = $serviceModel->serviceMoney($service,$off);
        $money = $bounty + $service_money;

        return $money;
    }

    static function employbounty($money, $task_id, $uid, $code, $type = 2)
    {
        $status = DB::transaction(function () use ($money, $task_id, $uid, $code, $type) {
            
            $query = DB::table('user_detail')->where('uid', '=', $uid);
            $query->where(function ($query) {
                $query->where('balance_status', '!=', 1);
            })->decrement('balance', $money);
            
            $data = self::where('id', $task_id)->update(['bounty_status' => 1]);
            
            $financial = [
                'action' => 1,
                'pay_type' => $type,
                'cash' => $money,
                'uid' => $uid,
                'created_at' => date('Y-m-d H:i:s', time())
            ];
            FinancialModel::create($financial);
            
            OrderModel::where('code', $code)->update(['status' => 1]);

            
            self::where('id', '=', $task_id)->update(['status' => 0]);

            
            UserDetailModel::where('uid', $uid)->increment('publish_task_num', 1);
        });

        return is_null($status) ? true : false;
    }

    
    static function bounty($money, $task_id, $uid, $code, $type = 1)
    {
        $status = DB::transaction(function () use ($money, $task_id, $uid, $code, $type) {
            
            $query = DB::table('user_detail')->where('uid', '=', $uid);
            $query->where(function ($query) {
                $query->where('balance_status', '!=', 1);
            })->decrement('balance', $money);
            
            self::where('id', $task_id)->update(['bounty_status' => 1]);
            
            $financial = [
                'action' => 1,
                'pay_type' => $type,
                'cash' => $money,
                'uid' => $uid,
                'created_at' => date('Y-m-d H:i:s', time())
            ];
            FinancialModel::create($financial);

            if($type == 1){
                
                OrderModel::where('code', $code)->update(['status' => 1]);
            }

            
            $taskService = TaskServiceModel::where('task_id',$task_id)->get()->toArray();
            if(!empty($taskService)){
                foreach($taskService as $k => $v){
                    $server = ServiceModel::where('id', $v['service_id'])->first();
                    if ($server['identify'] == 'ZHIDING') {
                        self::where('id', $task_id)->increment('top_status',1);
                    }
                    if ($server['identify'] == 'JIAJI') {
                        self::where('id', $task_id)->increment('top_status',1);
                    }
                    if ($server['identify'] == 'SOUSUOYINGQINGPINGBI') {
                        self::where('id', $task_id)->update(['engine_status' => 1]);
                    }
                    if ($server['identify'] == 'GAOJIANPINGBI') {
                        self::where('id', $task_id)->update(['work_status' => 1]);
                    }
                }
            }

            $task = TaskModel::find($task_id);
            
            
            $bounty_limit = \CommonClass::getConfig('task_bounty_limit');
            if ($bounty_limit < $task['bounty']) {
                self::where('id', '=', $task_id)->update(['status' => 3]);
            } else {
                self::where('id', '=', $task_id)->update(['status' => 2]);
            }
            
            UserDetailModel::where('uid', $uid)->increment('publish_task_num', 1);

        });
        
        if (is_null($status)) {
            
            $task_publish_success = MessageTemplateModel::where('code_name', 'task_publish_success')->where('is_open', 1)->first();
            if ($task_publish_success) {
                $task = self::where('id', $task_id)->first()->toArray();
                $task_status = [
                    'status' => [
                        0 => '暂不发布',
                        1 => '已经发布',
                        2 => '赏金托管',
                        3 => '审核通过',
                        4 => '威客交稿',
                        5 => '雇主选稿',
                        6 => '任务公示',
                        7 => '交付验收',
                        8 => '双方互评'
                    ]
                ];
                $task = \CommonClass::intToString([$task], $task_status);
                $task = $task[0];
                $user = UserModel::where('id', $uid)->first();
                $site_name = \CommonClass::getConfig('site_name');
                $domain = \CommonClass::getDomain();

                
                $messageVariableArr = [
                    'username' => $user['name'],
                    'task_number' => $task['id'],
                    'task_title' => $task['title'],
                    'task_status' => $task['status_text'],
                    'website' => $site_name,
                    'href' => $domain . '/task/' . $task['id'],
                    'task_link' => $task['title'],
                    'start_time' => $task['begin_at'],
                    'manuscript_end_time' => $task['delivery_deadline'],
                ];
                if($task_publish_success->is_on_site == 1){
                    \MessageTemplateClass::getMeaasgeByCode('task_publish_success',$user['id'],2,$messageVariableArr,$task_publish_success['name']);
                }

                if($task_publish_success->is_send_email == 1){
                    $email = $user->email;
                    \MessageTemplateClass::sendEmailByCode('task_publish_success',$email,$messageVariableArr,$task_publish_success['name']);
                }
                if($task_publish_success->is_send_mobile == 1 && $task_publish_success->code_mobile && $user->mobile){
                    $scheme = ConfigModel::phpSmsConfig('phpsms_scheme');
                    $templates = [
                        $scheme => $task_publish_success->code_mobile,
                    ];
                    $messageVariableArr = [
                        'username' => $user['name'],
                        'task_number' => $task['id'],
                        'task_title' => $task['title'],
                        'task_status' => $task['status_text'],
                        'website' => $site_name,
                        'task_link' => $task['title'],
                        'start_time' => $task['begin_at'],
                        'manuscript_end_time' => $task['delivery_deadline'],
                    ];
                    \SmsClass::sendSms($user->mobile, $templates, $messageVariableArr);
                }

            }
        }
        return is_null($status) ? true : false;
    }

    
    static function detail($id)
    {
        $query = self::select('task.*', 
 'b.name as type_name', 'c.name as cate_name')
            ->where('task.id', '=', $id);
        
        

		
		$query=$query->where(function($query){
			$query->where(function($querys){
				$querys->where('task.bounty_status',1)->where('b.alias','xuanshang');
			})->orwhere(function($querys){
				$querys->whereIn('task.bounty_status',[0,1])->where('b.alias','zhaobiao');
			});
		});
        $data = $query
            ->leftjoin('task_type as b', 'b.id', '=', 'task.type_id')
            ->leftjoin('cate as c', 'c.id', '=', 'task.cate_id')
            ->first();
        return $data;
    }


    
    static function findByCate($cate_id, $id)
    {
        $query = self::where('cate_id', '=', $cate_id);
        $query = $query->where(function ($query) use ($id) {
            $query->where('id', '!=', $id);
        });
        
        $query = $query->where(function ($query) {
            $query->where('status', '>', 2);
        });
        
        $query = $query->where(function ($query) {
            $query->where('delivery_deadline', '>', date('Y-m-d H:i:s', time()));
        });
        $data = $query->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        return $data;
    }

    
    static function isEmployer($task_id, $uid)
    {
        $data = self::where('id', $task_id)->first();
        if ($data['uid'] == $uid)
            return true;
        return false;
    }

    
    static public function distributeBounty($id, $uid)
    {
        
        $bounty = self::where('id', $id)->first();
        $bounty = ($bounty['bounty'] / $bounty['worker_num']) * (1 - sprintf("%.2f", $bounty['task_success_draw_ratio'] / 100));

        $status = DB::transaction(function () use ($bounty, $uid) {
            
            UserDetailModel::where('uid', $uid)->increment('balance', $bounty);
            
            $finance_data = [
                'action' => 2,
                'pay_type' => 1,
                'cash' => $bounty,
                'uid' => $uid,
                'created_at' => date('Y-m-d H:i:s', time())
            ];
            FinancialModel::create($finance_data);
        });

        return is_null($status) ? true : false;
    }


    
    static function employAccept($task, $type)
    {
        $status = DB::transeaction(function () use ($task, $type) {
            
            if ($type == 1) {
                
                TaskModel::where('id', $task['id'])->update(['status' => 3]);
                
                $employee_user = EmployUserModel::where('task_id', $task['id'])->first();
                
                self::distributeBounty($task['id'], $employee_user['uid']);
                $bounty = self::where('id', $task['id'])->first();
                $bounty = ($bounty['bounty'] / $bounty['worker_num']) * (1 - $bounty['task_success_draw_ratio']);
                
                UserDetailModel::where('uid', $employee_user['uid'])->increment('balance', $bounty);
                
                $finance_data = [
                    'action' => 2,
                    'pay_type' => 1,
                    'cash' => $bounty,
                    'uid' => $employee_user['uid'],
                    'created_at' => date('Y-m-d H:i:s', time())
                ];
                FinancialModel::create($finance_data);

            } else if ($type == 2) {

            }
        });
    }

    public function test($data)
    {
        $this->where('status','>',2);
    }

    
    static function buyServiceTaskBid($money, $task_id, $uid, $code, $type = 1)
    {
        $status = DB::transaction(function () use ($money, $task_id, $uid, $code, $type) {
            
            $query = DB::table('user_detail')->where('uid', '=', $uid);
            $query->where(function ($query) {
                $query->where('balance_status', '!=', 1);
            })->decrement('balance', $money);

            
            $financial = [
                'action' => 5,
                'pay_type' => $type,
                'cash' => $money,
                'uid' => $uid,
                'created_at' => date('Y-m-d H:i:s', time())
            ];
            FinancialModel::create($financial);
            
            $product = TaskServiceModel::where('task_id',$task_id)
                ->select('service_id')->get()->toArray();
            $product = array_flatten($product);

            if (!empty($product)) {
                foreach ($product as $k => $v) {
                    $server = ServiceModel::where('id', $v)->first();
                    if ($server['identify'] == 'ZHIDING') {
                        self::where('id', $task_id)->increment('top_status', 1);
                    }
                    if ($server['identify'] == 'JIAJI') {
                        self::where('id', $task_id)->increment('top_status', 1);
                    }
                    if ($server['identify'] == 'SOUSUOYINGQINGPINGBI') {
                        self::where('id', $task_id)->update(['engine_status' => 1]);
                    }
                    if ($server['identify'] == 'GAOJIANPINGBI') {
                        self::where('id', $task_id)->update(['work_status' => 1]);
                    }

                }
            }


            
            if($type == 1){
                OrderModel::where('code', $code)->update(['status' => 1]);
            }

        });

        return is_null($status) ? true : false;
    }


    
    static function bidBounty($money, $task_id, $uid, $code, $type = 1)
    {
        Log::info('支付金额'.$money.'任务id'.$task_id.'支付人'.$uid.'订单编号'.$code.'支付类型'.$type);
        $status = DB::transaction(function () use ($money, $task_id, $uid, $code, $type) {
            
            $query = DB::table('user_detail')->where('uid', '=', $uid);
            $query->where(function ($query) {
                $query->where('balance_status', '!=', 1);
            })->decrement('balance', $money);
            
            self::where('id', $task_id)->update(['bounty_status' => 1,'status' => 7,'updated_at' => date('Y-m-d H:i:s'),'publicity_at'=>date('Y-m-d H:i:s',time())]);
            
            $financial = [
                'action' => 1,
                'pay_type' => $type,
                'cash' => $money,
                'uid' => $uid,
                'created_at' => date('Y-m-d H:i:s', time())
            ];
            FinancialModel::create($financial);
            if($type == 1){
                
                OrderModel::where('code', $code)->update(['status' => 1]);
            }


            
            UserDetailModel::where('uid', $uid)->increment('publish_task_num', 1);
        });
        
        if (is_null($status)) {

            
            $work = WorkModel::where('task_id',$task_id)->where('status',1)->first();
            if(!empty($work)){
                $arr = [
                    'task_id' => $task_id,
                    'work_id' => $work['id']
                ];
                WorkModel::sendTaskWidMessage($arr);
            }
        }
        return is_null($status) ? true : false;
    }

    
    static public function myTaskCount($uid,$status,$typeId,$alias)
    {

        $tasks = TaskModel::where('task.uid',$uid);
        switch($alias) {
            case 'xuanshang':
                $tasks = $tasks->where('task.type_id', $typeId)->where('task.bounty_status', 1);
                break;
            case 'zhaobiao':
                $tasks = $tasks->where('task.type_id', $typeId);
                break;
            default:
                $tasks = $tasks->where('task.type_id', $typeId)->where('task.bounty_status', 1);
        }
        $count = $tasks->whereIn('task.status',$status)->count();
        return $count;
    }

    
    static public function myAcceptCount($taskIdArr,$status,$typeId,$alias)
    {
        $tasks = TaskModel::whereIn('id',$taskIdArr);
        switch($alias) {
            case 'xuanshang':
                $tasks = $tasks->where('type_id', $typeId)->where('task.bounty_status', 1);
                break;
            case 'zhaobiao':
                $tasks = $tasks->where('type_id', $typeId);
                break;
            default:
                $tasks = $tasks->where('type_id', $typeId)->where('task.bounty_status', 1);
        }
        $count = $tasks->whereIn('status',$status)->count();
        return $count;
    }

    
    static public function dealTaskArr($taskArr)
    {
        $taskIdArr = array_pluck($taskArr,'id');
        if(!empty($taskIdArr)){
            
            $service = ServiceModel::where('type',1)->select('id','identify')->get()->toArray();
            $newService = array_reduce($service,function(&$newService,$v){
                $newService[$v['id']] = $v['identify'];
                return $newService;
            });
            
            $taskTypeArr = TaskTypeModel::select('id','alias')->get()->toArray();
            $taskType = array_reduce($taskTypeArr,function(&$taskType,$v){
                $taskType[$v['alias']] = $v['id'];
                return $taskType;
            });
            $taskTypeA = array_reduce($taskTypeArr,function(&$taskTypeA,$v){
                $taskTypeA[$v['id']] = $v['alias'];
                return $taskTypeA;
            });
            
            $order = OrderModel::select('order.*','task.type_id')
                ->whereIn('order.task_id',$taskIdArr)
                ->where('order.status',1)
                ->where(function($query) use ($taskType){
                    $query->where(function($query) use ($taskType){
                        $query->where('task.type_id',$taskType['xuanshang']);
                    })->orWhere(function($query) use ($taskType){
                        $query->where('task.type_id',$taskType['zhaobiao'])
                            ->where('order.code','like','ts%');
                    });
                })
                ->leftJoin('task','task.id','=','order.task_id')
                ->get()->toArray();
            $taskIdArr = array_keys(\CommonClass::keyByGroup($order,'task_id'));
            $taskService = TaskServiceModel::whereIn('task_id',$taskIdArr)
                ->select('task_id','service_id')->get()->toArray();
            $newTaskService = array_reduce($taskService,function(&$newTaskService,$v) use ($newService) {
                if(in_array($v['service_id'],array_keys($newService))){
                    $newTaskService[$v['task_id']][] = $newService[$v['service_id']];
                }

                return $newTaskService;
            });
            
            if(!empty($newTaskService)){
                foreach($taskArr as $k => $v){
                    foreach($newTaskService as $k1 => $v1){
                        if($v['id'] == $k1){
                            $taskArr[$k]['task_service'] = $v1;
                        }
                    }
                }
            }
            foreach($taskArr as $k => $v){
                if(!isset($v['task_service'])){
                    $taskArr[$k]['task_service'] = [];
                }
                if($v['bounty_status'] == 1){
                    $taskArr[$k]['bounty_status_desc'] = '已托管';
                }else{
                    $taskArr[$k]['bounty_status_desc'] = '未托管';
                }
                if(!isset($v['task_type']) && in_array($v['type_id'],array_keys($taskTypeA))){
                    $taskArr[$k]['task_type'] = $taskTypeA[$v['type_id']];
                }
                if($v['bounty_status'] == 0){
                    if(isset($v['task_type']) && $v['task_type'] == 'zhaobiao'){
                        $taskArr[$k]['bounty'] = '可议价';
                    }elseif(!isset($v['task_type']) && in_array($v['type_id'],array_keys($taskTypeA)) && $taskTypeA[$v['type_id']]=='zhaobiao'){
                        $taskArr[$k]['bounty'] = '可议价';
                    }

                }

            }
        }
        return $taskArr;
    }







}
