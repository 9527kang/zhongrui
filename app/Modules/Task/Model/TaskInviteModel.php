<?php

namespace App\Modules\Task\Model;

use App\Modules\Manage\Model\ConfigModel;
use App\Modules\Manage\Model\MessageTemplateModel;
use App\Modules\User\Model\CommentModel;
use App\Modules\User\Model\DistrictModel;
use App\Modules\User\Model\UserModel;
use App\Modules\User\Model\UserTagsModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class TaskInviteModel extends Model
{

    
    protected $table = 'task_invite';
    public  $timestamps = false;  
    public $fillable = ['id','task_id','uid'];

    
    static public function getUserByTaskId($taskId,$paginate=6,$data=[])
    {
        $task = TaskModel::find($taskId);
        $list = UserModel::select('users.id','users.name','user_detail.avatar', 'user_detail.shop_status','shop.id as shopId','user_detail.province','user_detail.city');

        if(isset($data['cate_id']) && $data['cate_id']){
            $uidCateArr = UserTagsModel::where('skill_tags.cate_id',$data['cate_id'])->leftJoin('skill_tags','skill_tags.id','=','tag_user.tag_id')->select('tag_user.uid')->get()->toArray();
            $uidCateArr = array_flatten($uidCateArr);
            $list = $list->whereIn('users.id',$uidCateArr);
        }else{
            if(isset($data['pid']) && $data['pid']){
                $cateIdArr = TaskCateModel::findByPid([$data['pid']]);
                $cateIdArr = array_pluck($cateIdArr,'id');
                $uidCateArr = UserTagsModel::whereIn('skill_tags.cate_id',$cateIdArr)->leftJoin('skill_tags','skill_tags.id','=','tag_user.tag_id')->select('tag_user.uid')->get()->toArray();
                $uidCateArr = array_flatten($uidCateArr);
                $list = $list->whereIn('users.id',$uidCateArr);
            }
        }

        if(isset($data['city']) && $data['city']){
            $list = $list->where('user_detail.city',$data['city']);
        }else{
            if(isset($data['province']) && $data['province']){
                $list = $list->where('user_detail.province',$data['province']);
            }
        }
        if(isset($data['service_name']) && $data['service_name']){
            $list = $list->where('users.name','like','%'.$data['service_name'].'%');
        }
         $list = $list->leftJoin('user_detail', 'users.id', '=', 'user_detail.uid')
            ->leftJoin('shop','user_detail.uid','=','shop.uid')->where('users.status','<>', 2)->where('users.id','!=',$task['uid'])
            ->paginate($paginate)->setPath('/task/ajaxInviteUser');;
        if(!empty($list->toArray()['data'])){
            
            $inviteUser = TaskInviteModel::where('task_id',$taskId)->select('uid')->get()->toArray();
            $uidArr = array_flatten($inviteUser);
            $provinceId = array_pluck($list->toArray()['data'],'province');
            $cityId = array_pluck($list->toArray()['data'],'city');
            $districtId = array_merge($provinceId,$cityId);
            $district = DistrictModel::whereIn('id',$districtId)->select('id','name')->get()->toArray();
            $district = \CommonClass::setArrayKey($district,'id');
            $arrUid = array_pluck($list->toArray()['data'],'id');
            
            $comment = CommentModel::whereIn('to_uid',$arrUid)->groupBy('to_uid')->get(['to_uid',DB::raw('COUNT(*) as value')])->toArray();
            $comment = \CommonClass::setArrayKey($comment,'to_uid');
            $goodComment = CommentModel::whereIn('to_uid',$arrUid)->where('type',1)->groupBy('to_uid')->get(['to_uid',DB::raw('COUNT(*) as value')])->toArray();
            $goodComment = \CommonClass::setArrayKey($goodComment,'to_uid');
            foreach($list as $k => $v){
                $province = '';
                if($v['province'] && in_array($v['province'],array_keys($district))){
                    $province = $district[$v['province']]['name'];
                }
                $city = '';
                if($v['city'] && in_array($v['city'],array_keys($district))){
                    $city = $district[$v['city']]['name'];
                }
                $v->address = $province.$city;
                $v->is_invite = false;
                if(in_array($v['id'],$uidArr)){
                    $v->is_invite = true;
                }
                $commentUser = 0;
                if(!empty($comment) && in_array($v['id'],array_keys($comment))){
                    $commentUser = $comment[$v['id']]['value'];
                }
                $goodCommentUser = 0;
                if(!empty($goodComment) && in_array($v['id'],array_keys($goodComment))){
                    $goodCommentUser = $goodComment[$v['id']]['value'];
                }
                $v->percent = 100;
                $v->comment = $commentUser;
                $v->good_comment = $goodCommentUser;
                if($commentUser>0){
                    $v->percent = ceil($goodCommentUser/$commentUser*100);
                }
            }

        }
        return $list;
    }

    
    static public function sendInviteMsg($task,$uid)
    {
        $template = MessageTemplateModel::where('code_name', 'invite_user')->where('is_open', 1)->first();
        if ($template) {
            $user = UserModel::where('id', $uid)->first();
            $employerUser = UserModel::where('id', $task['uid'])->first();
            $domain = \CommonClass::getDomain();
            
            
            $messageVariableArr = [
                'username' => $user['name'],
                'employername' => $employerUser['name'],
                'title' => '<a href="'.$domain.'/task/'. $task['id'].'">'.$task['title'].'</a>',
            ];
            if($template->is_on_site == 1){
                \MessageTemplateClass::getMeaasgeByCode('invite_user',$user['id'],1,$messageVariableArr,$template['name']);
            }
            
            if($template->is_send_email == 1){
                $email = $user->email;
                \MessageTemplateClass::sendEmailByCode('invite_user',$email,$messageVariableArr,$template['name']);
            }
            if($template->is_send_mobile == 1 && $template->code_mobile && $user->mobile){
                $scheme = ConfigModel::phpSmsConfig('phpsms_scheme');
                $templates = [
                    $scheme => $template->code_mobile,
                ];
                $messageVariableArr = [
                    'username'     => $user['name'],
                    'employername' => $employerUser['name'],
                    'title'        => $task['title'],
                ];

                \SmsClass::sendSms($user->mobile, $templates, $messageVariableArr);
            }
        }
    }
}
