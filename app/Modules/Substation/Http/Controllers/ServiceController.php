<?php

namespace App\Modules\Substation\Http\Controllers;

use App\Http\Controllers\SubstationController;
use App\Http\Requests;
use App\Modules\Manage\Model\SubstationModel;
use App\Modules\Task\Model\TaskCateModel;
use App\Modules\User\Model\AuthRecordModel;
use App\Modules\User\Model\CommentModel;
use App\Modules\User\Model\TagsModel;
use App\Modules\User\Model\UserModel;
use App\Modules\User\Model\UserTagsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ServiceController extends SubstationController
{
    public function __construct()
    {
        parent::__construct();
        $this->user = Auth::user();
        $this->initTheme('substation');
        
    }

    
    public function getService(Request $request,$substationId)
    {
        
        $substation = SubstationModel::where('district_id', $substationId)->first();

        if(!empty($substation)){
            $substationName = $substation->name;
        }else{
            $substationName = '全国';
        }
        if(Session::get('substation_name')){
            Session::forget('substation_name');
            Session::put('substation_name',$substationName);
        }else{
            Session::put('substation_name',$substationName);
        }
        $this->theme->set('substationID',$substationId);
        $this->theme->set('substationNAME',$substationName);
        $this->theme->setTitle($substationName.'服务商');

        $merge = $request->all();

        $list = UserModel::select('user_detail.sign', 'users.name', 'user_detail.avatar', 'users.id','users.email_status',
            'user_detail.employee_praise_rate','user_detail.shop_status','shop.is_recommend','shop.id as shopId')
            ->leftJoin('user_detail', 'users.id', '=', 'user_detail.uid')
            ->leftJoin('shop','user_detail.uid','=','shop.uid')->where('users.status','<>', 2);

        if($request->get('service_name')){
            $searchName = $request->get('service_name');
            $list = $list->where('users.name','like',"%".$searchName."%");
        }
        
        if ($request->get('category')) {
            $category = TaskCateModel::findByPid([$request->get('category')]);

            if (empty($category)) {
                $category_data = TaskCateModel::findById($request->get('category'));
                $category = TaskCateModel::findByPid([$category_data['pid']]);
                $pid = $category_data['pid'];
                $arrTag = TagsModel::where('cate_id', $request->get('category'))->lists('id')->toArray();
                $dataUid = UserTagsModel::whereIn('tag_id', $arrTag)->lists('uid')->toArray();
                $list = $list->whereIn('users.id', $dataUid);
            } else {
                foreach ($category as $item){
                    $arrCateId[] = $item['id'];
                }
                $arrTag = TagsModel::whereIn('cate_id', $arrCateId)->lists('id')->toArray();
                $dataUid = UserTagsModel::whereIn('tag_id', $arrTag)->lists('uid')->toArray();
                $list = $list->whereIn('users.id', $dataUid);
                $pid = $request->get('category');
            }
        } else {
            
            $category = TaskCateModel::findByPid([0]);
            $pid = 0;
        }

        
        if($request->get('employee_praise_rate') && $request->get('employee_praise_rate') == 1){
            $list = $list->orderby('user_detail.employee_praise_rate','DESC');
        }
        $paginate = 10;
        
        $this->substation = $substationId;
        $list = $list->where(function($list){
            $list->where('user_detail.city',$this->substation)->orWhere('shop.city',$this->substation)
                ->orwhere('user_detail.province',$this->substation)->orwhere('shop.province',$this->substation);
        });
        $list = $list->orderBy('shop.is_recommend','DESC')->paginate($paginate);
        if (!empty($list->toArray()['data'])){

            foreach ($list as $k => $v){
                $arrUid[] = $v->id;
            }
        } else {
            $arrUid = 0;
        }

        
        $comment = CommentModel::whereIn('to_uid',$arrUid)->get()->toArray();
        if(!empty($comment)){
            
            $newComment = array_reduce($comment,function(&$newComment,$v){
                $newComment[$v['to_uid']][] = $v;
                return $newComment;
            });
            $commentCount = array();
            if(!empty($newComment)){
                foreach($newComment as $c => $d){
                    $commentCount[$c]['to_uid'] = $c;
                    $commentCount[$c]['count'] = count($d);
                }
            }
            
            $goodComment = CommentModel::whereIn('to_uid',$arrUid)->where('type',1)->get()->toArray();
            
            $newGoodsComment = array_reduce($goodComment,function(&$newGoodsComment,$v){
                $newGoodsComment[$v['to_uid']][] = $v;
                return $newGoodsComment;
            });
            $goodCommentCount = array();
            if(!empty($newGoodsComment)){
                foreach($newGoodsComment as $a => $b){
                    $goodCommentCount[$a]['to_uid'] = $a;
                    $goodCommentCount[$a]['count'] = count($b);
                }
            }
            
            foreach($list as $key => $value){
                foreach($goodCommentCount as $a => $b){
                    if($value['id'] == $b['to_uid']){
                        $list[$key]['good_comment_count'] = $b['count'];
                    }
                }
                foreach($commentCount as $c => $d){
                    if($value['id'] == $d['to_uid']){
                        $list[$key]['comment_count'] = $d['count'];
                    }
                }
            }
            foreach ($list as $key => $item) {
                
                if($item->comment_count > 0){
                    $item->percent = ceil($item->good_comment_count/$item->comment_count*100);
                }
                else{
                    $item->percent = 100;
                }
            }
        }else{
            foreach ($list as $key => $item) {
                
                $item->percent = 100;
            }
        }

        
        $arrSkill = UserTagsModel::getTagsByUserId($arrUid);

        if(!empty($arrSkill) && is_array($arrSkill)){
            foreach ($arrSkill as $item){
                $arrTagId[] = $item['tag_id'];
            }

            $arrTagName = TagsModel::select('id', 'tag_name')->whereIn('id', $arrTagId)->get()->toArray();
            foreach ($arrSkill as $item){
                foreach ($arrTagName as $value){
                    if ($item['tag_id'] == $value['id']){
                        $arrUserTag[$item['uid']][] = $value['tag_name'];
                    }
                }
            }
            foreach ($list as $key => $item){
                foreach ($arrUserTag as $k => $v){
                    if ($item->id == $k){
                        $list[$key]['skill'] = $v;
                    }
                }
            }
        }

        
        $userAuthOne = AuthRecordModel::whereIn('uid', $arrUid)->where('status', 2)->whereIn('auth_code',['bank','alipay'])->get()->toArray();
        $userAuthTwo = AuthRecordModel::whereIn('uid', $arrUid)->where('status', 1)
            ->whereIn('auth_code',['realname','enterprise'])->get()->toArray();
        $userAuth = array_merge($userAuthOne,$userAuthTwo);
        $auth = array();
        if(!empty($userAuth) && is_array($userAuth)){
            foreach($userAuth as $a => $b){
                foreach($userAuth as $c => $d){
                    if($b['uid'] = $d['uid']){
                        $auth[$b['uid']][] = $d['auth_code'];
                    }
                }
            }
        }
        if(!empty($auth) && is_array($auth)){
            foreach($auth as $e => $f){
                $auth[$e]['uid'] = $e;
                if(in_array('realname',$f)){
                    $auth[$e]['realname'] = true;
                }else{
                    $auth[$e]['realname'] = false;
                }
                if(in_array('bank',$f)){
                    $auth[$e]['bank'] = true;
                }else{
                    $auth[$e]['bank'] = false;
                }
                if(in_array('alipay',$f)){
                    $auth[$e]['alipay'] = true;
                }else{
                    $auth[$e]['alipay'] = false;
                }
                if(in_array('enterprise',$f)){
                    $auth[$e]['enterprise'] = true;
                }else{
                    $auth[$e]['enterprise'] = false;
                }
            }
            foreach ($list as $key => $item) {
                
                foreach ($auth as $a => $b) {
                    if ($item->id == $b['uid']) {
                        $list[$key]['auth'] = $b;
                    }
                }
            }
        }

        
        $newShop = UserModel::select('user_detail.sign', 'users.name', 'user_detail.avatar', 'users.id',
            'users.email_status','user_detail.employee_praise_rate','user_detail.shop_status','shop.is_recommend','shop.id as shopId')
            ->leftJoin('user_detail', 'users.id', '=', 'user_detail.uid')
            ->leftJoin('shop','users.id','=','shop.uid')
            ->where('users.status','<>', 2)
            ->where(function($list){
                $list->where('user_detail.city',$this->substation)->orWhere('shop.city',$this->substation)
                    ->orwhere('user_detail.province',$this->substation)->orwhere('shop.province',$this->substation);
            })
            ->orderBy('shop.created_at','DESC')
            ->limit(5)->get()->toArray();
        if(count($newShop)){
            foreach($newShop as $k=>$v){
                $comment = CommentModel::where('to_uid',$v['id'])->count();
                $goodComment = CommentModel::where('to_uid',$v['id'])->where('type',1)->count();
                if($comment){
                    $v['percent'] = intval(($goodComment/$comment)*100);
                }
                else{
                    $v['percent'] = 100;
                }
                $newShop[$k] = $v;
            }
            $hotList = $newShop;
        }
        else{
            $hotList = [];
        }

        $this->theme->set('menu_type',2);
        $data = array(
            'pid' => $pid,
            'category' => $category,
            'list' => $list,
            'merge' => $merge,
            'paginate' => $paginate,
            'page' => $request->get('page') ? $request->get('page') : '',
            'skillId' => $request->get('skillId') ? $request->get('skillId') : '',
            'type' => $request->get('type') ? $request->get('type') : 0,
            'hotList' => $hotList,
            'substation_id' => $substationId,
            'substation_name' => $substationName
        );
        return $this->theme->scope('substation.service', $data)->render();
    }
}
