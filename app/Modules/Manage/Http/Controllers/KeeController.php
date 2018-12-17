<?php
namespace App\Modules\Manage\Http\Controllers;


use App\Http\Controllers\ManageController;
use App\Modules\Manage\Model\ConfigModel;
use App\Modules\Task\Model\TaskCateModel;
use App\Modules\User\Model\TaskModel;
use Illuminate\Http\Request;
use Theme;

class KeeController extends ManageController
{

    public function __construct()
    {
        parent::__construct();

        $this->initTheme('manage');
    }

    
    public function keeLoad(Request $request)
    {
        $this->theme->setTitle('接入交付台');
        
        $keeKey = '';
        $keeKeyRule = ConfigModel::where('alias', 'kee_key')->first();
        if($keeKeyRule && !empty($keeKeyRule['rule'])){
            $keeKey = $keeKeyRule['rule'];
        }
        $status = 100;
        if(!empty($keeKey)){
            $url = \CommonClass::getConfig('kee_path').'KPPWStateForKee?key='.$keeKey;
            $result = json_decode(\CommonClass::sendGetRequest($url),true);
            if($result['code'] == 1000){
                $status = $result['data']['statue'];
            }
        }
        $isOpen  = 1; 
        
        $openKeeRule = ConfigModel::where('alias', 'open_kee')->first();
        if($status == 1){
            if($openKeeRule){
                $isOpen = $openKeeRule['rule'];
            }else{
                ConfigModel::create(['alias'=> 'open_kee','rule'=> 1]);
            }
        }else{
            $isOpen = 0;
            if($openKeeRule){
                ConfigModel::where('alias', 'open_kee')->update(['rule'=> 0]);

            }else{
                ConfigModel::create(['alias'=> 'open_kee','rule'=> 0]);
            }
        }

        $view = [
            'kee_status' => $status,
            'is_open' => $isOpen
        ];
        return $this->theme->scope('manage.keeload.index', $view)->render();
    }

    
    public function keeLoadFirst(Request $request)
    {
        $serviceSiteUrl = \CommonClass::getConfig('site_url');
        $serviceLogo = \CommonClass::getConfig('site_logo');
        $serviceName = \CommonClass::getConfig('company_name');
        $serviceContactInfo = \CommonClass::getConfig('contact_phone');
        $serviceContactEmail = \CommonClass::getConfig('contact_email');
        
        $data = [
            'serviceSiteUrl' => $serviceSiteUrl,
            'serviceName' => $serviceName,
            'serviceContacts' => '',
            'serviceContactInfo' => $serviceContactInfo,
            'serviceContactEmail' => $serviceContactEmail,
            'serviceLogo' => $serviceLogo,
            'serviceType' => 'pfkppw',
        ];
        $url = \CommonClass::getConfig('kee_path').'KPPWApplyForKee';
        $result = json_decode(\CommonClass::sendPostRequest($url,json_encode($data)),true);
        if($result['code'] == 1000){
            
            $keeKeyRule = ConfigModel::where('alias', 'kee_key')->first();
            if($keeKeyRule){
                $res = ConfigModel::where('alias', 'kee_key')->update(['rule'=> $result['data']['key']]);
            }else{
                $res = ConfigModel::create(['alias'=> 'kee_key','rule'=> $result['data']['key']]);
            }
            if($res){
                return redirect('/manage/keeLoad')->with(['message' => '申请成功']);
            }else{
                return redirect('/manage/keeLoad')->with(['message' => '申请失败']);
            }

        }else{
            return redirect('/manage/keeLoad')->with(['message' => '申请失败']);
        }
    }

    
    public function keeLoadAgain(Request $request)
    {
        
        $keeKeyRule = ConfigModel::where('alias', 'kee_key')->first();
        if($keeKeyRule){
            $keeKey = $keeKeyRule['rule'];
        }else{
            $keeKey = '';
        }
        if($keeKey){
            $url = \CommonClass::getConfig('kee_path').'KPPWAgainApplyForKee?key='.$keeKey;
            $result = json_decode(\CommonClass::sendGetRequest($url),true);
            if($result['code'] == 1000){
                return redirect('/manage/keeLoad')->with(['message' => '申请成功']);
            }else{
                return redirect('/manage/keeLoad')->with(['message' => '申请失败']);
            }
        }

    }


    
    public function isOpenKee(Request $request)
    {
        $value = $request->get('value');
        $info = [
            'rule' => $value
        ];
        $res = ConfigModel::where('alias','open_kee')->update($info);
        if($res){
            $arr = [
                'code' => 1,
                'msg' => 'success',
            ];
            return $arr;
        }else{
            $arr = [
                'code' => 0,
                'msg' => 'failure',
            ];
            return $arr;
        }
    }

    public function thirdTask(Request $request)
    {
        $this->theme->setTitle('第三方项目');
        
        $ruleDetail = 0;
        $rule = ConfigModel::where('alias', 'third_task')->first();
        if($rule && !empty($rule['rule'])){
            $ruleDetail = $rule['rule'];
        }
        $task  = [];
        $category_all = [];
        $category_second = [];
        $category = [];
        if($ruleDetail == 1){
            $task = TaskModel::where('type',1);
            if($request->get('name')){
                $task = $task->where('title','like','%'.trim($request->get('name')).'%');
            }
            if($request->get('from')){
                $task = $task->where('site_name','like','%'.trim($request->get('from')).'%');
            }
            if($request->get('start')){
                $start = preg_replace('/([\x80-\xff]*)/i', '', $request->get('start'));
                $start = date('Y-m-d 00:00:00',strtotime($start));
                $task = $task->where('created_at','>',$start);
            }
            if($request->get('end')){
                $end = preg_replace('/([\x80-\xff]*)/i', '', $request->get('end'));
                $end = date('Y-m-d 23:59:59',strtotime($end));
                $task = $task->where('created_at','<',$end);
            }
            $task = $task->orderBy('id','desc')->paginate(10);

            if(!empty($task->toArray()['data'])){
                $cateId = array_pluck($task->toArray()['data'],'cate_id');
                $category = TaskCateModel::findById($cateId);
                $category = \CommonClass::setArrayKey($category,'id');
            }

            $category_all = TaskCateModel::findByPid([0]);
            $category_second = TaskCateModel::findByPid([$category_all[0]['id']]);
        }


        $view = [
            'rule'  => $ruleDetail,
            'list'  => $task,
            'merge' => $request->all(),
            'category_all'    => $category_all,
            'category_second' => $category_second,
            'category' => $category
        ];
        return $this->theme->scope('manage.keeload.thirdTask', $view)->render();
    }

    public function updateTag(Request $request)
    {
        $merge = $request->all();
        if(!isset($merge['taskId']) || empty($merge['taskId'])){
            return [
                'code' => 0,
                'msg'  => '请选择操作项目'
            ];
        }
        $taskId = explode(',',$merge['taskId']);
        if($request->get('type') == 1){
            $res = TaskModel::whereIn('id',$taskId)->update([
                'cate_id' => isset($merge['cate_id']) ? $merge['cate_id'] : '',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }elseif($request->get('type') == 2){
            $res = TaskModel::whereIn('id',$taskId)->where('status',0)->where('cate_id','>',0)->update([
                'status' => 4,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }else{
            $res = TaskModel::whereIn('id',$taskId)->delete();
        }

        if($res){
            return [
                'code' => 1,
                'msg'  => '操作成功'
            ];
        }
        return [
            'code' => 0,
            'msg'  => '操作失败'
        ];
    }











}