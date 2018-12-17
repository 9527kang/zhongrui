<?php
namespace App\Modules\Manage\Http\Controllers;

use App\Http\Controllers\ManageController;
use App\Modules\Finance\Model\FinancialModel;
use App\Modules\Manage\Model\ConfigModel;
use App\Modules\Manage\Model\FastTaskModel;
use App\Modules\Manage\Model\MessageTemplateModel;
use App\Modules\Manage\Model\ServiceModel;
use App\Modules\Task\Model\TaskAttachmentModel;
use App\Modules\Task\Model\TaskCateModel;
use App\Modules\Task\Model\TaskExtraSeoModel;
use App\Modules\Task\Model\TaskModel;
use App\Modules\Task\Model\TaskTypeModel;
use App\Modules\Task\Model\WorkCommentModel;
use App\Modules\Task\Model\WorkModel;
use App\Modules\User\Model\AttachmentModel;
use App\Modules\User\Model\DistrictModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use App\Modules\User\Model\UserMoreModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Theme;
use Excel;

class TaskController extends ManageController
{
    public function __construct()
    {
        parent::__construct();

        $this->initTheme('manage');
        $this->theme->setTitle('任务列表');
        $this->theme->set('manageType', 'task');
    }

    
    public function taskList(Request $request)
    {
        $search = $request->all();
        $by = $request->get('by') ? $request->get('by') : 'id';
        $order = $request->get('order') ? $request->get('order') : 'desc';
        $paginate = $request->get('paginate') ? $request->get('paginate') : 10;
        $taskType=TaskTypeModel::select('id')->where('alias',"xuanshang")->first();
        $taskList = TaskModel::select('task.id', 'us.name', 'task.title', 'task.created_at', 'task.status', 'task.verified_at', 'task.bounty_status')
                    ->where('type_id',$taskType['id']);
        if ($request->get('task_title')) {
            $taskList = $taskList->where('task.title', 'like', '%' . $request->get('task_id') . '%');
        }
        if ($request->get('username')) {
            $taskList = $taskList->where('us.name', 'like', '%' . e($request->get('username')) . '%');
        }
        
        if ($request->get('status') && $request->get('status') != 0) {
            switch ($request->get('status')) {
                case 1:
                    $status = [0];
                    break;
                case 2:
                    $status = [1, 2];
                    break;
                case 3:
                    $status = [3, 4, 5, 6, 7, 8];
                    break;
                case 4:
                    $status = [9];
                    break;
                case 5:
                    $status = [10];
                    break;
                case 6:
                    $status = [11];
                    break;
            }
            $taskList = $taskList->whereIn('task.status', $status);
        }
        
        if ($request->get('time_type')) {
            if ($request->get('start')) {
                $start = preg_replace('/([\x80-\xff]*)/i', '', $request->get('start'));
                $start = date('Y-m-d H:i:s',strtotime($start));
                $taskList = $taskList->where($request->get('time_type'), '>', $start);
            }
            if ($request->get('end')) {
                $end = preg_replace('/([\x80-\xff]*)/i', '', $request->get('end'));
                $end = date('Y-m-d H:i:s',strtotime($end));
                $taskList = $taskList->where($request->get('time_type'), '<', $end);
            }

        }
        $taskList = $taskList->orderBy($by, $order)
            ->leftJoin('users as us', 'us.id', '=', 'task.uid')
            ->paginate($paginate);

        $data = array(
            'task' => $taskList,
        );
        $data['merge'] = $search;

        return $this->theme->scope('manage.tasklist', $data)->render();
    }

    
    public function taskHandle($id, $action)
    {
        $domain = \CommonClass::getDomain();
        if (!$id) {
            return \CommonClass::showMessage('参数错误');
        }
        $id = intval($id);

        switch ($action) {
            
            case 'pass':
                $status = 3;
                break;
            
            case 'deny':
                $status = 10;
                break;
        }
        
        $task = TaskModel::where('id', $id)->first();
        $user = UserModel::where('id', $task['uid'])->first();
        $site_name = \CommonClass::getConfig('site_name');
        if ($status == 3) {
            $result = TaskModel::where('id', $id)->whereIn('status', [1, 2])->update(array('status' => $status,'verified_at'=>date('Y-m-d H:i:s')));
            if (!$result) {
                return redirect()->back()->with(['error' => '操作失败！']);
            }
            $task_audit_failure = MessageTemplateModel::where('code_name', 'audit_success')->where('is_open', 1)->first();
            if ($task_audit_failure) {
                
                $messageVariableArr = [
                    'username' => $user['name'],
                    'website' => $site_name,
                    'task_number' => $task['id'],
                    'task_link' => $domain . '/task/' . $task['id']
                ];
                if($task_audit_failure->is_on_site == 1){
                    \MessageTemplateClass::getMeaasgeByCode('audit_success',$user['id'],2,$messageVariableArr,$task_audit_failure['name']);
                }

                if($task_audit_failure->is_send_email == 1){
                    $email = $user->email;
                    \MessageTemplateClass::sendEmailByCode('audit_success',$email,$messageVariableArr,$task_audit_failure['name']);
                }
                if($task_audit_failure->is_send_mobile == 1 && $task_audit_failure->code_mobile && $user->mobile){
                    $scheme = ConfigModel::phpSmsConfig('phpsms_scheme');
                    $templates = [
                        $scheme => $task_audit_failure->code_mobile,
                    ];

                    \SmsClass::sendSms($user->mobile, $templates, $messageVariableArr);
                }

            }
        } elseif ($status == 10) {
            $result = DB::transaction(function () use ($id, $status, $task) {
                TaskModel::where('id', $id)->whereIn('status', [1, 2])->update(array('status' => $status,'verified_at'=>date('Y-m-d H:i:s')));
                
                if ($task['bounty_status'] == 1) {
                    UserDetailModel::where('uid', $task['uid'])->increment('balance', $task['bounty']);
                    
                    $finance = [
                        'action' => 7,
                        'pay_type' => 1,
                        'cash' => $task['bounty'],
                        'uid' => $task['uid'],
                        'created_at' => date('Y-m-d H:i:d', time()),
                        'updated_at' => date('Y-m-d H:i:d', time())
                    ];
                    FinancialModel::create($finance);
                }
            });
            if (!is_null($result)) {
                return redirect()->back()->with(['error' => '操作失败！']);
            }
            $task_audit_failure = MessageTemplateModel::where('code_name', 'task_audit_failure')->where('is_open', 1)->where('is_on_site', 1)->first();
            if ($task_audit_failure) {
                
                $messageVariableArr = [
                    'username' => $user['name'],
                    'href' => $domain.'/task/'.$task['id'],
                    'task_title' => $site_name,
                    'website' => $site_name,
                ];
                if($task_audit_failure->is_on_site == 1){
                    \MessageTemplateClass::getMeaasgeByCode('task_audit_failure',$user['id'],2,$messageVariableArr,$task_audit_failure['name']);
                }

                if($task_audit_failure->is_send_email == 1){
                    $email = $user->email;
                    \MessageTemplateClass::sendEmailByCode('task_audit_failure',$email,$messageVariableArr,$task_audit_failure['name']);
                }
                if($task_audit_failure->is_send_mobile == 1 && $task_audit_failure->code_mobile && $user->mobile){
                    $scheme = ConfigModel::phpSmsConfig('phpsms_scheme');
                    $templates = [
                        $scheme => $task_audit_failure->code_mobile,
                    ];
                    $messageVariableArr = [
                        'username' => $user['name'],
                        'task_title' => $site_name,
                        'website' => $site_name,
                    ];
                    \SmsClass::sendSms($user->mobile, $templates, $messageVariableArr);
                }
            }

        }
        return redirect()->back()->with(['message' => '操作成功！']);
    }


    
    public function taskMultiHandle(Request $request)
    {
        if (!$request->get('ckb')) {
            return redirect()->back()->with(['error'=>'操作成功！']);
        }
        switch ($request->get('action')) {
            case 'pass':
                $status = 3;
                break;
            case 'deny':
                $status = 10;
                break;
            default:
                $status = 3;
                break;
        }
        TaskModel::whereIn('task.id', $request->get('ckb'))->join('task_type','task_type.id','=','task.type_id')->Where(function($query){
			  $query->where(function($querys){
				  $querys->where('task_type.alias','xuanshang')->where('task.status',2);
			  })->orwhere(function($querys){
				 $querys->where('task_type.alias','zhaobiao')->where('task.status',1);
			  });
		})->update(array('task.status' => $status));
		return redirect()->back()->with(['message'=>'操作成功！']);

    }

    
    public function taskDetail($id)
    {
        $task = TaskModel::where('id', $id)->first();
        if (!$task) {
            return redirect()->back()->with(['error' => '当前任务不存在，无法查看稿件！']);
        }
        $query = TaskModel::select('task.*', 'us.name as nickname', 'ud.avatar', 'ud.qq')->where('task.id', $id);
        $taskDetail = $query->join('user_detail as ud', 'ud.uid', '=', 'task.uid')
            ->leftjoin('users as us', 'us.id', '=', 'task.uid')
            ->first()->toArray();
        if (!$taskDetail) {
            return redirect()->back()->with(['error' => '当前任务已经被删除！']);
        }
        $status = [
            0 => '暂不发布',
            1 => '已经发布',
            2 => '赏金托管',
            3 => '审核通过',
            4 => '威客交稿',
            5 => '雇主选稿',
            6 => '任务公示',
            7 => '交付验收',
            8 => '双方互评',
            9 => '任务完成',
            10 => '失败',
            11 => '维权'
        ];
        $taskDetail['status_text'] = $status[$taskDetail['status']];

        
        $taskType = TaskTypeModel::all();
        
        $taskDelivery = WorkModel::where('task_id', $id)->where('status', 3)->count();
        
        $task_attachment = TaskAttachmentModel::select('task_attachment.*', 'at.url')->where('task_id', $id)
            ->leftjoin('attachment as at', 'at.id', '=', 'task_attachment.attachment_id')->get()->toArray();
        
        $task_seo = TaskExtraSeoModel::where('task_id', $id)->first();
        
        $works = WorkModel::select('work.*', 'us.name as nickname', 'ud.avatar')
            ->where('work.status', '<=', 1)
            ->where('work.task_id', $id)
            ->with('childrenAttachment')
            ->leftjoin('user_detail as ud', 'ud.uid', '=', 'work.uid')
            ->leftjoin('users as us', 'us.id', '=', 'work.uid')
            ->get()->toArray();

        
        $task_massages = WorkCommentModel::select('work_comments.*', 'us.name as nickname', 'ud.avatar')
            ->leftjoin('user_detail as ud', 'ud.uid', '=', 'work_comments.uid')
            ->leftjoin('users as us', 'us.id', '=', 'work_comments.uid')
            ->where('work_comments.task_id', $id)->paginate();
        
        $work_delivery = WorkModel::select('work.*', 'us.name as nickname', 'ud.mobile', 'ud.qq', 'ud.avatar')
            ->whereIn('work.status', [2, 3])
            ->where('work.task_id', $id)
            ->with('childrenAttachment')
            ->leftjoin('user_detail as ud', 'ud.uid', '=', 'work.uid')
            ->leftjoin('users as us', 'us.id', '=', 'work.uid')
            ->get()->toArray();

        $domain = \CommonClass::getDomain();

        $data = [
            'task' => $taskDetail,
            'domain' => $domain,
            'taskType' => $taskType,
            'taskDelivery' => $taskDelivery,
            'taskAttachment' => $task_attachment,
            'task_seo' => $task_seo,
            'works' => $works,
            'task_massages' => $task_massages,
            'work_delivery' => $work_delivery
        ];
        return $this->theme->scope('manage.taskdetail', $data)->render();
    }

    
    public function taskDetailUpdate(Request $request)
    {
        $data = $request->except('_token');
        $task_extra = [
            'task_id' => intval($data['task_id']),
            'seo_title' => $data['seo_title'],
            'seo_keyword' => $data['seo_keyword'],
            'seo_content' => $data['seo_content'],
        ];
        $result = TaskExtraSeoModel::firstOrCreate(['task_id' => $data['task_id']])
            ->where('task_id', $data['task_id'])
            ->update($task_extra);
        
        $task = [
            'title' => $data['title'],
            'desc' => $data['desc'],
            'phone' => $data['phone']
        ];
        
        $task_result = TaskModel::where('id', $data['task_id'])->update($task);

        if (!$result || !$task_result) {
            return redirect()->back()->with(['error' => '更新失败！']);
        }

        return redirect()->back()->with(['massage' => '更新成功！']);
    }

    
    public function taskMassageDelete($id)
    {
        $result = WorkCommentModel::destroy($id);

        if (!$result) {
            return redirect()->to('/manage/taskList')->with(['error' => '留言删除失败！']);
        }
        return redirect()->to('/manage/taskList')->with(['massage' => '留言删除成功！']);
    }

    

    public function download($id)
    {
        $pathToFile = AttachmentModel::where('id', $id)->first();
        $pathToFile = $pathToFile['url'];
        if(is_file($pathToFile)){
            return response()->download($pathToFile);
        }else{
            return redirect()->back()->with(['error' => '附件不存在']);
        }

    }


    
    public function fastPub(Request $request)
    {
        $merge = $request->all();
        $list = FastTaskModel::whereRaw('1=1');
        if($request->get('name')){
            $list = $list->where('users.name','like','%'.$request->get('name').'%');
        }
        if($request->get('email')){
            $list = $list->where('users.email','like','%'.$request->get('email').'%');
        }
        if($request->get('mobile')){
            $list = $list->where('fast_task.mobile','like','%'.$request->get('mobile').'%');
        }
        if($request->get('status') || $request->get('status') == '0'){
            $list = $list->where('fast_task.status',$request->get('status'));
        }
        $paginate = 15;
        $page = $request->get('page') ? $request->get('page') : 1;
        $list = $list->leftJoin('users','users.id','=','fast_task.uid')->select('fast_task.*','users.name','users.mobile','users.email')->orderBy('fast_task.created_at','desc')->paginate($paginate);
        $views = [
            'list'     => $list,
            'merge'    => $merge,
            'page'     => $page,
            'paginate' => $paginate
        ];
        return $this->theme->scope('manage.fasttasklist', $views)->render();
    }

    public function deleteFast($id)
    {
        FastTaskModel::where('id',$id)->delete();
        return redirect()->back()->with(['message' => '操作成功']);
    }

    
    public function pubTask(Request $request)
    {
        $data = $request->all();

        $taskInfo = FastTaskModel::find($data['id']);

        $isKee = ConfigModel::isOpenKee();

        
        $category_all = TaskCateModel::findByPid([0]);
        $category_second = TaskCateModel::findByPid([$category_all[0]['id']]);

        
        $province = DistrictModel::findTree(0);
        
        $city = DistrictModel::findTree($province[0]['id']);
        
        $area = DistrictModel::findTree($city[0]['id']);
        
        $service = ServiceModel::where('status',1)->where('type',1)->get()->toArray();

        
        $taskType = [
            'xuanshang','zhaobiao'
        ];
        $rewardModel = TaskTypeModel::whereIn('alias',$taskType)->get()->toArray();

        $view = [
            'data'            => $data,
            'task_info'       => $taskInfo,
            'category_all'    => $category_all,
            'category_second' => $category_second,
            'province'        => $province,
            'city'            => $city,
            'area'            => $area,
            'service'         => $service,
            'rewardModel'     => $rewardModel,
            'isKee'           => $isKee,
        ];
        return $this->theme->scope('manage.fasttask', $view)->render();

    }

    public function saveFastTask(Request $request)
    {
        $data = $request->except('_token','id');

        $data['desc'] = \CommonClass::removeXss($data['desc']);
        $data['created_at'] = date('Y-m-d H:i:s', time());
        if($data['region_limit'] == 0){
            $data['province'] = '';
            $data['city'] = '';
            $data['area'] = '';
        }

        $isKee = ConfigModel::isOpenKee();
        
        $data['type_id'] = TaskTypeModel::getTaskTypeIdByAlias($data['type_alias']);
        $data['kee_status'] = 0;

        
        switch($data['type_alias']){
            case 'xuanshang':
                $data['status'] = 1;
                
                $task_percentage = \CommonClass::getConfig('task_percentage');
                $task_fail_percentage = \CommonClass::getConfig('task_fail_percentage');
                break;
            case 'zhaobiao':
                $data['status'] = 3;
                $task_percentage = \CommonClass::getConfig('bid_percentage');
                $task_fail_percentage = \CommonClass::getConfig('bid_fail_percentage');
                $data['kee_status'] = ($isKee && isset($data['is_to_kee']) && $data['is_to_kee'] == 1) ? 1 : 0;
                break;
            default:
                $data['status'] = 1;
                $task_percentage = \CommonClass::getConfig('task_percentage');
                $task_fail_percentage = \CommonClass::getConfig('task_fail_percentage');
                break;
        }

        $data['task_success_draw_ratio'] = $task_percentage; 
        $data['task_fail_draw_ratio'] = $task_fail_percentage;

        $data['begin_at'] = date('Y-m-d H:i:s');
        $data['delivery_deadline'] = preg_replace('/([\x80-\xff]*)/i', '', $data['delivery_deadline_'.$data['type_alias']]);
        $data['begin_at'] = date('Y-m-d H:i:s', strtotime($data['begin_at']));
        $data['delivery_deadline'] = date('Y-m-d H:i:s', strtotime($data['delivery_deadline']));
        $data['bounty'] = $data['bounty_'.$data['type_alias']];
        $data['show_cash'] = $data['bounty'];
        $data['worker_num'] = $data['worker_num_'.$data['type_alias']];
        $data['file_id'] = isset($data['file_ids']) ? $data['file_ids'] : [];

        $taskModel = new TaskModel();
        $result = $taskModel->createTask($data);
        if (!$result) {
            return redirect()->back()->with('error', '创建任务失败！');
        }
        FastTaskModel::where('id',$request->get('id'))->update([
            'task_id'    => $result['id'],
            'task_type'  => $data['type_id'],
            'status'     => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return redirect('/manage/fastPub')->with(['message' => '保存成功']);
    }

}
