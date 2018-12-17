<?php

namespace App\Modules\Api\Http\Controllers;

use App\Http\Requests;
use App\Modules\Employ\Models\EmployCommentsModel;
use App\Modules\Employ\Models\EmployGoodsModel;
use App\Modules\Employ\Models\EmployModel;
use App\Modules\Employ\Providers\EmployServiceProvider;
use App\Modules\Manage\Model\ConfigModel;
use App\Modules\Order\Model\ShopOrderModel;
use App\Modules\Shop\Models\GoodsCommentModel;
use App\Modules\Shop\Models\GoodsModel;
use App\Modules\Shop\Models\ShopModel;
use App\Modules\Shop\Models\ShopTagsModel;
use App\Modules\Task\Model\SuccessCaseModel;
use App\Modules\Task\Model\TaskCateModel;
use App\Modules\User\Model\AuthRecordModel;
use App\Modules\User\Model\CommentModel;
use App\Modules\User\Model\DistrictModel;
use App\Modules\User\Model\EnterpriseAuthModel;
use App\Modules\User\Model\RealnameAuthModel;
use App\Modules\User\Model\SkillTagsModel;
use App\Modules\User\Model\TagsModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use App\Modules\User\Model\UserTagsModel;
use Guzzle\Tests\Http\CommaAggregatorTest;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiBaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use App\Modules\Shop\Models\ShopFocusModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShopController extends ApiBaseController
{
    
    public function collectShop(Request $request){
        $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
        $shopId = $request->get('shop_id');
        $uid = $tokenInfo['uid'];
        $shopInfo = ShopModel::where(['uid' => $uid,'id' => $shopId,'status' => 1])->first();
        if(!empty($shopInfo)){
            return $this->formateResponse(1007,'不能收藏自己的店铺');
        }
        $data = [
            'uid' => $uid,
            'shop_id' => $shopId,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $res = ShopFocusModel::create($data);
        if($res){
            return $this->formateResponse(1000,'收藏成功',$res);
        }else{
            return $this->formateResponse(1008,'收藏失败');
        }
    }


    
    public function cancelCollect(Request $request){
        $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
        $shopId = $request->get('shop_id');
        $uid = $tokenInfo['uid'];
        $res = ShopFocusModel::where(['uid' => $uid,'shop_id' => $shopId])->delete();
        if($res){
            return $this->formateResponse(1000,'取消成功');
        }else{
            return $this->formateResponse(1009,'取消失败');
        }
    }


    
    public function collectStatus(Request $request){
        if(!$request->get('token')){
            $status = 0;
        }else{
            $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
            $uid = $tokenInfo['uid'];
            $shopId = $request->get('shop_id');
            $shopFocusInfo = ShopFocusModel::where(['uid' => $uid,'shop_id' => $shopId])->first();
            if(empty($shopFocusInfo)){
                $status = 0;
            }else{
                $status = 1;
            }
        }
        return $this->formateResponse(1000,'获取店铺被收藏状态成功',['status' => $status]);
    }


    
    public function isEmploy(Request $request){
        if(!$request->get('token')){
            return $this->formateResponse(1010,'请先登录');
        }
        $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
        $uid = $tokenInfo['uid'];
        if($uid == $request->get('id')){
            return $this->formateResponse(1011,'您不能雇佣你自己');
        }
        return $this->formateResponse(1000,'success');
    }

    
    public function shopInfo(Request $request){
        $shopId = intval($request->get('shop_id'));
        $shopInfo = ShopModel::where(['id' => $shopId,'status' => 1])->select('id','uid','shop_pic','shop_name','shop_bg','province','city')->first();
        if(empty($shopInfo)){
            return $this->formateResponse(1012,'传送数据错误');
        }
        $userInfo = UserModel::where('id',$shopInfo->uid)->where('status','<>',2)->select('name')->first();
        if(empty($userInfo)){
            return $this->formateResponse(1013,'用户id不存在');
        }
        $userDetail = UserDetailModel::where('uid',$shopInfo->uid)->select('avatar')->first();
        if(empty($userDetail)){
            return $this->formateResponse(1014,'用户信息不存在');
        }
        $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
        
        $shopInfo->avatar = $userDetail->avatar?$domain->rule.'/'.$userDetail->avatar:$userDetail->avatar;
        $shopInfo->shop_desc = htmlspecialchars_decode($shopInfo->shop_desc);
        $shopInfo->shop_pic = $shopInfo->shop_pic?$domain->rule.'/'.$shopInfo->shop_pic:$shopInfo->shop_pic;
        $shopInfo->shop_bg = $shopInfo->shop_bg?$domain->rule.'/'.$shopInfo->shop_bg:$shopInfo->shop_bg;
        $shopInfo->cate_name = [];
        $shopTags = ShopTagsModel::where('shop_id',$shopId)->select('tag_id')->get()->toArray();
        if(!empty($shopTags)){
            $tagIds = array_unique(array_flatten($shopTags));
            $tags = SkillTagsModel::whereIn('id',$tagIds)->select('tag_name')->get()->toArray();
            if(!empty($tags)){
                $shopInfo->cate_name = array_unique(array_flatten($tags));
            }
        }
        $shopInfo->username = '';
        if($shopInfo->uid){
            $user = UserModel::where('id',$shopInfo->uid)->first();
            if(!empty($user)){
                $shopInfo->username = $user->name;
            }
        }
        
        $shopInfo->city_name = DistrictModel::getAreaName($shopInfo->province,$shopInfo->city);
        
        
        $companyInfo = EnterpriseAuthModel::where('uid', $shopInfo->uid)->where('status',1)->orderBy('created_at', 'desc')->first();
        if($companyInfo){
            $shopInfo->isEnterprise = 1;
        }else{
            $shopInfo->isEnterprise = 0;
        }
        
       
        
       
        return $this->formateResponse(1000,'获取威客店铺信息成功',$shopInfo);


    }


    
    public function workList(Request $request){
        $shopId = $request->get('shop_id');
        $shopInfo = ShopModel::where(['id' => $shopId,'status' => 1])->select('shop_name','shop_pic','uid')->first();
        if(empty($shopInfo)){
            return $this->formateResponse(1015,'传送参数错误');
        }
        $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
        $goodsList = GoodsModel::where(['shop_id' => $shopId,'type' => 1,'status' => 1,'is_delete' => 0])
            ->orderBy('sales_num','desc')
            ->orderBy('created_at','desc')
            ->select('id','title','unit','cash','cover','sales_num')
            ->paginate(5)
            ->toArray();
        
        $goodsId = array_pluck($goodsList['data'],'id');
        
        $comment = GoodsCommentModel::where('uid','!=',$shopInfo['uid'])->whereIn('goods_id',$goodsId)->select('type','goods_id')->get()->toArray();
        
        $commentArr = array_reduce($comment,function(&$commentArr,$v){
            $commentArr[$v['goods_id']]['total'][] = $v;
            if($v['type'] == 0){
                $commentArr[$v['goods_id']]['good'][] = $v;
            }
            return $commentArr;
        });
        if($goodsList['total']){
           foreach($goodsList['data'] as $k=>$v){
               $goodsList['data'][$k]['cover'] = $v['cover']?$domain->rule.'/'.$v['cover']:$v['cover'];
               switch($v['unit']){
                   case '0':
                       $goodsList['data'][$k]['unit'] = '件';
                       break;
                   case '1':
                       $goodsList['data'][$k]['unit'] = '时';
                       break;
                   case '2':
                       $goodsList['data'][$k]['unit'] = '份';
                       break;
                   case '3':
                       $goodsList['data'][$k]['unit'] = '个';
                       break;
                   case '4':
                       $goodsList['data'][$k]['unit'] = '张';
                       break;
                   case '5':
                       $goodsList['data'][$k]['unit'] = '套';
                       break;
               }
               if(!empty($commentArr)){
                   foreach($commentArr as $k1 => $v1){
                       if($v['id'] == $k1){
                           if(isset($v1['total'])){
                               $goodsList['data'][$k]['total_comment'] = count($v1['total']);
                           }else{
                               $goodsList['data'][$k]['total_comment'] = 0;
                           }
                           if(isset($v1['good'])){
                               $goodsList['data'][$k]['good_comment'] = count($v1['good']);
                           }else{
                               $goodsList['data'][$k]['good_comment'] = 0;
                           }

                       }
                   }
               }
           }

            foreach($goodsList['data'] as $k => $v){
                if(isset($v['total_comment']) && $v['total_comment'] > 0 && isset($v['good_comment'])){
                    $goodsList['data'][$k]['percent'] = ceil($v['good_comment']/$v['total_comment']*100);
                }else{
                    $goodsList['data'][$k]['percent'] = 100;
                }
                unset($goodsList['data'][$k]['good_comment'],$goodsList['data'][$k]['total_comment']);
            }
        }
        return $this->formateResponse(1000,'获取商品信息成功',$goodsList);

    }

    
    public function serviceList(Request $request){
        $shopId = $request->get('shop_id');
        $shopInfo = ShopModel::where(['id' => $shopId,'status' => 1])->select('shop_name','shop_pic','uid')->first();
        if(empty($shopInfo)){
            return $this->formateResponse(1015,'传送参数错误');
        }
        $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
        $goodsList = GoodsModel::where(['shop_id' => $shopId,'type' => 2,'status' => 1,'is_delete' => 0])
                ->orderBy('created_at','desc')
                ->select('id','title','cash','cover','sales_num','cate_id','unit')
                ->paginate(5)
                ->toArray();
        
        $goodsId = array_pluck($goodsList['data'],'id');
        
        $comment = GoodsCommentModel::where('uid','!=',$shopInfo['uid'])->whereIn('goods_id',$goodsId)->select('type','goods_id')->get()->toArray();
        
        $commentArr = array_reduce($comment,function(&$commentArr,$v){
            $commentArr[$v['goods_id']]['total'][] = $v;
            if($v['type'] == 0){
                $commentArr[$v['goods_id']]['good'][] = $v;
            }
            return $commentArr;
        });
        if($goodsList['total']){
            $cate_ids = array_pluck($goodsList['data'],'cate_id');
            $cateInfo = TaskCateModel::whereIn('id',$cate_ids)->select('id','name')->get()->toArray();
            $cateInfo = collect($cateInfo)->pluck('name','id')->all();
            foreach($goodsList['data'] as $k=>$v){
                $goodsList['data'][$k]['cover'] = $v['cover']?$domain->rule.'/'.$v['cover']:$v['cover'];
                $goodsList['data'][$k]['cate_name'] = isset($cateInfo[$v['cate_id']])?$cateInfo[$v['cate_id']]:null;
                switch($v['unit']){
                    case '0':
                        $goodsList['data'][$k]['unit'] = '件';
                        break;
                    case '1':
                        $goodsList['data'][$k]['unit'] = '时';
                        break;
                    case '2':
                        $goodsList['data'][$k]['unit'] = '份';
                        break;
                    case '3':
                        $goodsList['data'][$k]['unit'] = '个';
                        break;
                    case '4':
                        $goodsList['data'][$k]['unit'] = '张';
                        break;
                    case '5':
                        $goodsList['data'][$k]['unit'] = '套';
                        break;
                }
                if(!empty($commentArr)){
                    foreach($commentArr as $k1 => $v1){
                        if($v['id'] == $k1){
                            if(isset($v1['total'])){
                                $goodsList['data'][$k]['total_comment'] = count($v1['total']);
                            }else{
                                $goodsList['data'][$k]['total_comment'] = 0;
                            }
                            if(isset($v1['good'])){
                                $goodsList['data'][$k]['good_comment'] = count($v1['good']);
                            }else{
                                $goodsList['data'][$k]['good_comment'] = 0;
                            }

                        }
                    }
                }
            }
            foreach($goodsList['data'] as $k => $v){
                if(isset($v['total_comment']) && $v['total_comment'] > 0 && isset($v['good_comment'])){
                    $goodsList['data'][$k]['percent'] = ceil($v['good_comment']/$v['total_comment']*100);
                }else{
                    $goodsList['data'][$k]['percent'] = 100;
                }
                unset($goodsList['data'][$k]['good_comment'],$goodsList['data'][$k]['total_comment']);
            }
        }
        return $this->formateResponse(1000,'获取服务信息成功',$goodsList);

    }


    
    public function successList(Request $request){
        $shopId = $request->get('shop_id');
        $shopInfo = ShopModel::where(['id' => $shopId,'status' => 1])->select('shop_name','shop_pic','uid')->first();
        if(empty($shopInfo)){
            return $this->formateResponse(1016,'传送参数错误');
        }
        $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
        $caseInfo = SuccessCaseModel::where('uid',$shopInfo['uid'])->select('id','pic','title')->orderBy('created_at','desc')
            ->paginate(6)->toArray();
        if($caseInfo['total']){
            foreach($caseInfo['data'] as $k=>$v){
                $caseInfo['data'][$k]['pic'] = $v['pic']?$domain->rule.'/'.$v['pic']:$v['pic'];
            }
        }
        return $this->formateResponse(1000,'获取成功案例信息成功',$caseInfo);
    }

    
    public function commentList(Request $request)
    {
        $shopId = $request->get('shop_id');
        $page = $request->get('page') ? $request->get('page') : 1;
        $type = $request->get('type') ? $request->get('type') : 0;
        $shopInfo = ShopModel::where(['id' => $shopId,'status' => 1])->select('shop_name','shop_pic','uid')->first();
        if(empty($shopInfo)){
            return $this->formateResponse(1016,'传送参数错误');
        }
        
        $employ = EmployModel::where('employee_uid',$shopInfo->uid)->select('id')->get()->toArray();
        $employId = array_flatten($employ);

        $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
        $goods = GoodsModel::select('cash','unit','id')->where('shop_id',$shopId)->get()->toArray();
        $goodsId = array_pluck($goods,'id');
        $goodsQuery = GoodsCommentModel::whereIn('goods_comment.goods_id',$goodsId)->where('goods_comment.uid','!=',$shopInfo['uid']);
        $employQuery = EmployCommentsModel::where('employ_comment.to_uid',$shopInfo['uid'])->whereIn('employ_comment.employ_id',$employId);
        switch($type){
            case 1:
                $goodsQuery = $goodsQuery->where('goods_comment.type',0);
                $employQuery = $employQuery->where('employ_comment.type',1);
                break;
            case 2:
                $goodsQuery = $goodsQuery->where('goods_comment.type',1);
                $employQuery = $employQuery->where('employ_comment.type',2);
                break;
            case 3:
                $goodsQuery = $goodsQuery->where('goods_comment.type',2);
                $employQuery = $employQuery->where('employ_comment.type',3);
                break;
        }
        $goodsCommentTotal = $goodsQuery->count();
        $employCommentTotal = $employQuery->count();
        $total = $goodsCommentTotal + $employCommentTotal;
        $perPage = 2;
        $halfPerPage = $perPage/2;
        $goodsComment = [];
        $employComment = [];

        $totalPage1 = floor($goodsCommentTotal/$halfPerPage);
        $totalPage2 = floor($employCommentTotal/$halfPerPage);
        if($totalPage1 >= $totalPage2){
            $totalPage = $totalPage2;
        }else{
            $totalPage = $totalPage1;
        }
        $goodsQuery = $goodsQuery->leftJoin('goods','goods.id','=','goods_comment.goods_id')->leftJoin('user_detail','user_detail.uid','=','goods_comment.uid')->leftJoin('users','users.id','=','goods_comment.uid')->select('goods_comment.*','users.name','user_detail.avatar','goods.cash as price','goods.unit');
        $employQuery = $employQuery->leftJoin('employ','employ.id','=','employ_comment.employ_id')->leftJoin('user_detail','user_detail.uid','=','employ_comment.from_uid')->leftJoin('users','users.id','=','employ_comment.from_uid')->select('employ_comment.*','users.name','user_detail.avatar','employ.bounty as price');

        if($page <= $totalPage){
            $goodsComment = $goodsQuery->paginate($halfPerPage)->toArray();
            $employComment = $employQuery->paginate($halfPerPage)->toArray();
        }else{
            $i = $page-$totalPage;
            if($totalPage1 >= $totalPage2){
                $goodsComment1 = $goodsQuery->paginate($halfPerPage,$columns = ['*'], $pageName = 'page1', $page+$i-1)->toArray();
                $goodsComment2 = $goodsQuery->paginate($halfPerPage,$columns = ['*'], $pageName = 'page2', $page+$i)->toArray();
                $goodsComment['data'] = array_merge($goodsComment1['data'],$goodsComment2['data']);


            }else{
                $employComment1 = $employQuery->paginate($halfPerPage,$columns = ['*'], $pageName = 'page1', $page+$i-1)->toArray();
                $employComment2 = $employQuery->paginate($halfPerPage,$columns = ['*'], $pageName = 'page2', $page+$i)->toArray();


                $employComment['data'] = array_merge($employComment1['data'],$employComment2['data']);

            }
        }
        if($totalPage1 >= $totalPage2){
            $total1 = $employQuery->paginate($halfPerPage,$columns = ['*'], $pageName = 'page3', $totalPage+1)->count();
        }else{
            $total1 = $goodsQuery->paginate($halfPerPage,$columns = ['*'], $pageName = 'page3', $totalPage+1)->count();
        }
        $total = $total -$total1;

        
        $allgoodsComment = GoodsCommentModel::whereIn('goods_id',$goodsId)->where('uid','!=',$shopInfo['uid'])->count();
        $goodgoodsComment = GoodsCommentModel::whereIn('goods_id',$goodsId)->where('uid','!=',$shopInfo['uid'])->where('type',0)->count();
        $midgoodsComment = GoodsCommentModel::whereIn('goods_id',$goodsId)->where('uid','!=',$shopInfo['uid'])->where('type',1)->count();
        $badgoodsComment = GoodsCommentModel::whereIn('goods_id',$goodsId)->where('uid','!=',$shopInfo['uid'])->where('type',2)->count();
        
        $allemployComment = EmployCommentsModel::where('to_uid',$shopInfo['uid'])->whereIn('employ_id',$employId)->count();
        $goodemployComment = EmployCommentsModel::where('to_uid',$shopInfo['uid'])->whereIn('employ_id',$employId)->where('type',1)->count();
        $midemployComment = EmployCommentsModel::where('to_uid',$shopInfo['uid'])->whereIn('employ_id',$employId)->where('type',2)->count();
        $bademployComment = EmployCommentsModel::where('to_uid',$shopInfo['uid'])->whereIn('employ_id',$employId)->where('type',3)->count();

        $allcomment = $allgoodsComment + $allemployComment;
        $goodcomment = $goodgoodsComment + $goodemployComment;
        $midcomment = $midgoodsComment + $midemployComment;
        $badcomment = $badgoodsComment + $bademployComment;

        $goodsComment['data'] = isset($goodsComment['data']) ? $goodsComment['data'] : [];
        $employComment['data'] = isset($employComment['data']) ? $employComment['data'] : [];
        $comment1 = [];
        if(!empty($goodsComment['data'])){
            foreach($goodsComment['data'] as $k => $v){
                $comment1[$k]['name'] = $v['name'];
                if($v['avatar']){
                    $comment1[$k]['avatar'] = $domain['rule'].'/'.$v['avatar'];
                }else{
                    $comment1[$k]['avatar'] = '';
                }

                $comment1[$k]['avg_score'] = number_format(($v['speed_score'] + $v['quality_score'] + $v['attitude_score'])/3,1);
                $comment1[$k]['comment_desc'] = $v['comment_desc'];
                $comment1[$k]['created_at'] = date('Y',strtotime($v['created_at'])).'年'.date('m',strtotime($v['created_at'])).'月'.date('d',strtotime($v['created_at'])).'日';
                switch($v['unit']){
                    case '0':
                        $unit = '件';
                        break;
                    case '1':
                        $unit = '时';
                        break;
                    case '2':
                        $unit = '份';
                        break;
                    case '3':
                        $unit = '个';
                        break;
                    case '4':
                        $unit = '张';
                        break;
                    case '5':
                        $unit = '套';
                        break;
                    default:
                        $unit = '件';
                }
                $comment1[$k]['desc'] = '成交'.$v['price'].'元/'.$unit;
            }
        }

        $comment2 = [];
        if(!empty($employComment['data'])){
            foreach($employComment['data'] as $k => $v){
                $comment2[$k]['name'] = $v['name'];
                if($v['avatar']){
                    $comment2[$k]['avatar'] = $domain['rule'].'/'.$v['avatar'];
                }else{
                    $comment2[$k]['avatar'] = '';
                }
                $comment2[$k]['avg_score'] = number_format(($v['speed_score'] + $v['quality_score'] + $v['attitude_score'])/3,1);
                $comment2[$k]['comment_desc'] = $v['comment'];
                $comment2[$k]['created_at'] = date('Y',strtotime($v['created_at'])).'年'.date('m',strtotime($v['created_at'])).'月'.date('d',strtotime($v['created_at'])).'日';

                $comment2[$k]['desc'] = '成交'.$v['price'].'元';
            }
        }

        $comment = [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total/$perPage),
            'all_comment' => $allcomment ,
            'good_comment' => $goodcomment,
            'mid_comment' => $midcomment,
            'bad_comment' => $badcomment ,
            'data' => array_merge($comment1,$comment2)
        ];


        
        $goodsCommentAtt = number_format(GoodsCommentModel::whereIn('goods_id',$goodsId)->avg('attitude_score'),1);
        $goodsCommentSpeed = number_format(GoodsCommentModel::whereIn('goods_id',$goodsId)->avg('speed_score'),1);
        $goodsCommentQuality = number_format(GoodsCommentModel::whereIn('goods_id',$goodsId)->avg('quality_score'),1);
        
        $employCommentAtt = number_format(EmployCommentsModel::where('to_uid',$shopInfo->uid)->whereIn('employ_id',$employId)->avg('attitude_score'),1);
        $employCommentSpeed = number_format(EmployCommentsModel::where('to_uid',$shopInfo->uid)->whereIn('employ_id',$employId)->avg('speed_score'),1);
        $employCommentQuality = number_format(EmployCommentsModel::where('to_uid',$shopInfo->uid)->whereIn('employ_id',$employId)->avg('quality_score'),1);

        if(($goodsCommentAtt> 0 || $goodsCommentSpeed > 0 || $goodsCommentQuality > 0) && ($employCommentAtt> 0 || $employCommentSpeed > 0 || $employCommentQuality > 0)){
            $score1= $goodsCommentAtt + $employCommentAtt;
            $score2= $goodsCommentSpeed + $employCommentSpeed;
            $score3= $goodsCommentQuality + $employCommentQuality;
            $totalScore = $score1 + $score2 + $score3;
            $comment['attitude_score'] = number_format($score1/2,1);
            $comment['speed_score'] = number_format($score2/2,1);
            $comment['quality_score'] = number_format($score3/2,1);
            $comment['avg_score'] = number_format($totalScore/6,1);
        }elseif($goodsCommentAtt> 0 || $goodsCommentSpeed > 0 || $goodsCommentQuality > 0){
            $score1= $goodsCommentAtt;
            $score2= $goodsCommentSpeed;
            $score3= $goodsCommentQuality;
            $totalScore = $score1 + $score2 + $score3;
            $comment['attitude_score'] = number_format($score1,1);
            $comment['speed_score'] = number_format($score2,1);
            $comment['quality_score'] = number_format($score3,1);
            $comment['avg_score'] = number_format($totalScore/3,1);
        }elseif($employCommentAtt> 0 || $employCommentSpeed > 0 || $employCommentQuality > 0){
            $score1= $employCommentAtt;
            $score2= $employCommentSpeed;
            $score3= $employCommentQuality;
            $totalScore = $score1 + $score2 + $score3;
            $comment['attitude_score'] = number_format($score1,1);
            $comment['speed_score'] = number_format($score2,1);
            $comment['quality_score'] = number_format($score3,1);
            $comment['avg_score'] = number_format($totalScore/3,1);
        }else{
            $comment['attitude_score'] = 0;
            $comment['speed_score'] = 0;
            $comment['quality_score'] = 0;
            $comment['avg_score'] = 0;
        }

        return $this->formateResponse(1000,'获取店铺评价信息成功',$comment);

    }

    
    public function goodDetail(Request $request){
        $type = $request->get('type');
        $id = $request->get('id');
        $goodDetail = GoodsModel::where(['id' => $id,'type' => $type,'status' => 1,'is_delete' => 0])->select('id','uid','shop_id','desc')->first();
        if(empty($goodDetail)){
            return $this->formateResponse(1017,'传送参数错误');
        }
        $desc = htmlspecialchars_decode($goodDetail->desc);
        $goodInfo = [
            'id' => $goodDetail->id,
            'uid' => $goodDetail->uid,
            'shop_id' => $goodDetail->shop_id,
            'desc' => $desc
        ];
        return $this->formateResponse(1000,'获取商品详情信息成功',$goodInfo);
    }


    
    public function goodComment(Request $request){
        $type = $request->get('type');
        $id = $request->get('id');
        if(!$id or !$type){
            return $this->formateResponse(1017,'传送参数不能为空');
        }
        $goodDetail = GoodsModel::where(['id' => $id,'type' => $type])->select('cash','unit','id','uid','shop_id')->first();
        if(empty($goodDetail)){
            return $this->formateResponse(1018,'传送参数错误');
        }

        switch($goodDetail->unit){
            case '0':
                $goodDetail->unit = '件';
                break;
            case '1':
                $goodDetail->unit = '时';
                break;
            case '2':
                $goodDetail->unit = '份';
                break;
            case '3':
                $goodDetail->unit = '个';
                break;
            case '4':
                $goodDetail->unit = '张';
                break;
            case '5':
                $goodDetail->unit = '套';
                break;
        }
        $commentInfo = [];
        if($type == 1){
            
            $good_num = GoodsCommentModel::where(['goods_id' => $id,'type' => 0])->count();
            $middle_num = GoodsCommentModel::where(['goods_id' => $id,'type' => 1])->count();
            $bad_num = GoodsCommentModel::where(['goods_id' => $id,'type' => 2])->count();
            $comment = GoodsCommentModel::where('goods_id',$id);
            if($request->get('sorts')){
                $sorts = $request->get('sorts');
                switch($sorts){
                    case '1':
                        $classify = 0;
                        $comment = $comment->where('type',$classify);
                        break;
                    case '2':
                        $classify = 1;
                        $comment = $comment->where('type',$classify);
                        break;
                    case '3':
                        $classify = 2;
                        $comment = $comment->where('type',$classify);
                        break;
                }

            }
            $comment = $comment->select('id','uid','speed_score','quality_score','attitude_score','comment_desc','type','created_at')->paginate(3)->toArray();

        }else{
            
            
            $employ_id = EmployGoodsModel::where('service_id', $id)->lists('employ_id')->toArray();
            $good_num = EmployCommentsModel::whereIn('employ_id', $employ_id)->where('to_uid',$goodDetail['uid'])->where('type',1)->count();
            $middle_num = EmployCommentsModel::whereIn('employ_id', $employ_id)->where('to_uid',$goodDetail['uid'])->where('type',2)->count();
            $bad_num = EmployCommentsModel::whereIn('employ_id', $employ_id)->where('to_uid',$goodDetail['uid'])->where('type',3)->count();

            
            $comment = EmployCommentsModel::whereIn('employ_id', $employ_id)->where('to_uid',$goodDetail['uid']);
            if($request->get('sorts') && in_array($request->get('sorts'),[1,2,3])){
                $comment = $comment->where('type',$request->get('sorts'));

            }
            $comment = $comment->select('id','from_uid as uid','speed_score','quality_score','attitude_score','comment as comment_desc','type','created_at')->paginate(3)->toArray();

        }
        if($comment['total']){
            $uids = array_pluck($comment['data'],'uid');
            $userInfo = UserModel::whereIn('id',$uids)->where('status',1)->select('id','name')->get()->toArray();

            $userInfo = collect($userInfo)->pluck('name','id')->all();
            $userDetail = UserDetailModel::whereIn('uid',$uids)->select('uid','avatar')->get()->toArray();

            $userDetail = collect($userDetail)->pluck('avatar','uid')->all();
            $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
            foreach($comment['data'] as $k=>$v){
                $comment['data'][$k]['name'] = $userInfo[$v['uid']];
                $comment['data'][$k]['avatar'] = $userDetail[$v['uid']]?$domain->rule.'/'.$userDetail[$v['uid']]:$userDetail[$v['uid']];
                $comment['data'][$k]['comment_desc'] = htmlspecialchars_decode($v['comment_desc']);
                $comment['data'][$k]['total_score'] = number_format(($v['speed_score']+$v['quality_score']+$v['attitude_score'])/3,1);
                $comment['data'][$k]['cash'] = $goodDetail->cash;
                $comment['data'][$k]['unit'] = $goodDetail->unit;
                $comment['data'][$k]['created_at'] = date('Y-m-d',strtotime($v['created_at']));
            }
            $commentInfo = $comment;

        }
        $commentList = [
            'good_id' => $goodDetail->id,
            'user_id' => $goodDetail->uid,
            'shop_id' => $goodDetail->shop_id,
            'good_num' => $good_num,
            'middle_num' => $middle_num,
            'bad_num' => $bad_num,
            'commentInfo' => $commentInfo
        ];
        return $this->formateResponse(1000,'获取商品评价信息成功',$commentList);
    }


    
    public function goodContent(Request $request){
        $id = $request->get('id');
        $type = $request->get('type');
        if(!$id or !$type){
            return $this->formateResponse(1021,'传送参数不能为空');
        }
        $goodInfo = GoodsModel::where(['id' => $id,'type' => $type])
            ->select('id','shop_id','title','unit','cash','cover','desc','sales_num','comments_num','good_comment','uid','status','is_delete')
            ->first();
        if(empty($goodInfo)){
            return $this->formateResponse(1022,'传送参数错误');
        }
        $goodInfo->desc = htmlspecialchars_decode($goodInfo->desc);
        if(in_array($goodInfo->status,['0','2','3']) || $goodInfo->is_delete == 1 ){
            $goodInfo->is_buy = 0; 
        }else{
            $goodInfo->is_buy = 1;
        }
        switch($goodInfo->unit){
            case '0':
                $goodInfo->unit = '件';
                break;
            case '1':
                $goodInfo->unit = '时';
                break;
            case '2':
                $goodInfo->unit = '份';
                break;
            case '3':
                $goodInfo->unit = '个';
                break;
            case '4':
                $goodInfo->unit = '张';
                break;
            case '5':
                $goodInfo->unit = '套';
                break;
        }
        $shopInfo = ShopModel::where(['id' => $goodInfo->shop_id,'status' => 1])->first();
        if(empty($shopInfo)){
            return $this->formateResponse(1023,'店铺信息不存在');
        }
        
        $user = UserModel::where('id',$goodInfo->uid)->first();
        if($user){
            $goodInfo->username = $user->name;
        }else{
            $goodInfo->username = '';
        }
        
        if($shopInfo->province){
            $province = DistrictModel::where('id',$shopInfo->province)->select('id','name')->first();
            $provinceName = $province->name;
        }else{
            $provinceName = '';
        }
        if($shopInfo->city){
            $city = DistrictModel::where('id',$shopInfo->city)->select('id','name')->first();
            $cityName = $city->name;
        }else{
            $cityName = '';
        }
        
        $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
        $goodInfo->cover = $goodInfo->cover?$domain->rule.'/'.$goodInfo->cover:$goodInfo->cover;
        $goodInfo->city_name = $provinceName.$cityName;
        
        
        if($goodInfo->comments_num > 0){
            $goodInfo->percent = ceil($goodInfo->good_comment/$goodInfo->comments_num*100);
        }
        else{
            $goodInfo->percent = 100;
        }
        $goodInfo->speed_score = 5;
        $goodInfo->quality_score = 5;
        $goodInfo->attitude_score = 5;
        $goodInfo->avg_score = 5;
        $commentArr['speed_score'] = [];
        $commentArr['quality_score'] = [];
        $commentArr['attitude_score'] = [];
        if($type == 1){
            $comment = GoodsCommentModel::where('goods_id',$id)->select('speed_score','quality_score','attitude_score')->get()->toArray();

            if(!empty($comment)){
                foreach($comment as $k => $v){
                    $commentArr['speed_score'][] = $v['speed_score'];
                    $commentArr['quality_score'][] = $v['quality_score'];
                    $commentArr['attitude_score'][] = $v['attitude_score'];
                }
                $goodInfo->speed_score = round(array_sum($commentArr['speed_score'])/count($commentArr['speed_score']),1);
                $goodInfo->quality_score = round(array_sum($commentArr['quality_score'])/count($commentArr['quality_score']),1);
                $goodInfo->attitude_score = round(array_sum($commentArr['attitude_score'])/count($commentArr['attitude_score']),1);
                $goodInfo->avg_score = round(($goodInfo->speed_score + $goodInfo->quality_score + $goodInfo->attitude_score)/3,1);
            }
        }else{
            $employ = EmployGoodsModel::where('service_id',$id)->select('employ_id')->get()->toArray();
            $employId = array_flatten($employ);
            $comment = EmployCommentsModel::whereIn('employ_id',$employId)->select('speed_score','quality_score','attitude_score')->where('to_uid',$goodInfo->uid)->get()->toArray();
            if($comment){
                foreach($comment as $k => $v){
                    $commentArr['speed_score'][] = $v['speed_score'];
                    $commentArr['quality_score'][] = $v['quality_score'];
                    $commentArr['attitude_score'][] = $v['attitude_score'];
                }
                $goodInfo->speed_score = round(array_sum($commentArr['speed_score'])/count($commentArr['speed_score']),1);
                $goodInfo->quality_score = round(array_sum($commentArr['quality_score'])/count($commentArr['quality_score']),1);
                $goodInfo->attitude_score = round(array_sum($commentArr['attitude_score'])/count($commentArr['attitude_score']),1);
                $goodInfo->avg_score = round(($goodInfo->speed_score + $goodInfo->quality_score + $goodInfo->attitude_score)/3,1);

            }
        }
        return $this->formateResponse(1000,'获取商品内容成功',$goodInfo);
    }


    
    public function shopList(Request $request){
        $name = $request->get('name');
        $cate_id = $request->get('cate_id');

        $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();

        $userList = UserModel::select('users.name','users.email_status','user_detail.uid','user_detail.avatar','user_detail.shop_status','shop.id','shop.shop_name','shop.shop_pic')
            ->leftJoin('user_detail', 'users.id', '=', 'user_detail.uid')
            ->leftJoin('shop','users.id','=','shop.uid')->where('users.status','<>', 2);
        if($name){
            $userList = $userList->where('users.name','like','%'.$name.'%');
        }
        if($cate_id && $cate_id > 0){
            
            $cate = TaskCateModel::where('id',$cate_id)->first();
            if($cate){
                if($cate->pid == 0){
                    
                    $childCate =  TaskCateModel::where('pid',$cate_id)->select('id')->get()->toArray();
                    $cateIdArr = array_flatten($childCate);
                    $usertags = UserTagsModel::join('skill_tags','tag_user.tag_id','=','skill_tags.id')
                        ->whereIn('skill_tags.cate_id',$cateIdArr)
                        ->select('tag_user.uid')->get()->toArray();
                }else{
                    $usertags = UserTagsModel::join('skill_tags','tag_user.tag_id','=','skill_tags.id')
                        ->where('skill_tags.cate_id',$cate_id)
                        ->select('tag_user.uid')->get()->toArray();
                }
                $userIds = array_unique(array_flatten($usertags));
                $userList = $userList->whereIn('users.id',$userIds);
            }else{
                return $this->formateResponse(2001,'参数错误');
            }
        }

        if($request->get('desc')){
            switch($request->get('desc')){
                case 1:
                    $userList = $userList->orderBy('shop.is_recommend','desc');
                    break;
                case 2:
                    $userList = $userList->orderby('user_detail.employee_praise_rate','desc');
                    break;
                case 3:
                    $userList = $userList->orderBy('users.created_at','desc');
                    break;

                default:
                    $userList = $userList->orderBy('shop.is_recommend','desc');
            }
        }else{
            
            $userList = $userList = $userList->orderBy('shop.is_recommend','desc');
        }
        $userList = $userList->paginate()->toArray();
        if($userList['data']){
            $userList['data'] = $this->dealUserArr($userList['data'],$domain);
        }

        if(empty($userList['data'])){
            $recommend = ShopModel::where('status',1)->select('id','uid','shop_pic','shop_name','total_comment','good_comment')->limit(5)->orderBy('created_at','desc')->get()->toArray();
            $userList['recommend'] = $this->dealShopArr($recommend,$domain);
        }
        return $this->formateResponse(1000,'获取服务商信息成功',$userList);
    }


    public function dealUserArr($userArr,$domain)
    {
        
        $uidArr = array_pluck($userArr,'uid');
        $userInfoTags = UserTagsModel::whereIn('uid',$uidArr)->get()->toArray();
        if(!empty($userInfoTags)){
            
            $tagIds = array_unique(array_pluck($userInfoTags,'tag_id'));
            
            $tags = SkillTagsModel::whereIn('id',$tagIds)->select('id','tag_name')->get()->toArray();
            $tagsArr = [];
            foreach($tags as $key=>$value) {
                $tagsArr[$value['id']] = $value['tag_name'];
            }
            $userInfoTags = collect($userInfoTags)->groupBy('uid')->toArray();
            $userInfoDetail = [];
            foreach($userInfoTags as $key=>$value) {
                foreach($value as $k=>$v){
                    $userInfoDetail[$key][] = isset($tagsArr[$v['tag_id']])?$tagsArr[$v['tag_id']]:'';
                }
            }

        }

        
        $provinceInfo = UserDetailModel::join('district', 'user_detail.province', '=', 'district.id')
            ->select('user_detail.uid','district.name')
            ->whereIn('user_detail.uid', $uidArr)
            ->get()->toArray();
        $cityInfo = UserDetailModel::join('district', 'user_detail.city', '=', 'district.id')
            ->select('user_detail.uid','district.name')
            ->whereIn('user_detail.uid', $uidArr)
            ->get()->toArray();
        $provinceInfo = collect($provinceInfo)->pluck('name','id')->all();
        $cityInfo = collect($cityInfo)->pluck('name','id')->all();

        foreach($userArr as $k=>$v){
            if(!empty($v['shop_pic']) && $v['shop_status'] == 1){
                $userArr[$k]['shop_open'] = 1;
                $userArr[$k]['shop_pic'] = $v['shop_pic']?$domain->rule.'/'.$v['shop_pic']:$v['shop_pic'];
            }else{
                $userArr[$k]['shop_open'] = 0;
                $userArr[$k]['shop_pic'] = $v['avatar']?$domain->rule.'/'.$v['avatar']:$v['avatar'];
                $userArr[$k]['shop_name'] = $v['name'];
            }
            if($v['email_status'] == 2){
                $userArr[$k]['email'] = 1;
            }else{
                $userArr[$k]['email'] = 0;
            }
            unset($userArr[$k]['name'],$userArr[$k]['avatar'],$userArr[$k]['email_status']);

            if(isset($provinceInfo[$v['id']])){
                $cityeName = $provinceInfo[$v['id']];
            }else{
                $cityeName = '';
            }
            if(isset($cityInfo[$v['id']])){
                $cityeName.=$cityInfo[$v['id']];
            }else{
                $cityeName.='';
            }

            $userArr[$k]['city_name'] = $cityeName;
            $userArr[$k]['cate_name'] = isset($userInfoDetail[$v['uid']])?array_values($userInfoDetail[$v['uid']]):[];
        }

        
        $comment = CommentModel::whereIn('to_uid',$uidArr)->get()->toArray();
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
            
            $goodComment = CommentModel::whereIn('to_uid',$uidArr)->where('type',1)->get()->toArray();
            
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
            
            foreach($userArr as $key => $value){
                foreach($goodCommentCount as $a => $b){
                    if($value['uid'] == $b['to_uid']){
                        $userArr[$key]['good_comment'] = $b['count'];
                    }
                }
                foreach($commentCount as $c => $d){
                    if($value['uid'] == $d['to_uid']){
                        $userArr[$key]['total_comment'] = $d['count'];
                    }
                }
            }
            foreach ($userArr as $key => $item) {
                if(!isset($item['good_comment'])){
                    $userArr[$key]['good_comment'] = 0;
                }
                if(!isset($item['total_comment'])){
                    $userArr[$key]['total_comment'] = 0;
                }
                
                if(isset($userArr[$key]['total_comment']) && $userArr[$key]['total_comment'] > 0){
                    $userArr[$key]['percent'] = intval(($userArr[$key]['good_comment'] /$userArr[$key]['total_comment']*100));
                }
                else{
                    $userArr[$key]['percent'] = 100;
                }
            }
        }else{
            foreach ($userArr as $key => $item) {
                if(!isset($item['good_comment'])){
                    $userArr[$key]['good_comment'] = 0;
                }
                if(!isset($item['total_comment'])){
                    $userArr[$key]['total_comment'] = 0;
                }
                
                $userArr[$key]['percent'] = 100;
            }
        }

        if(!empty($uidArr)){
            
            $userAuthOne = AuthRecordModel::whereIn('uid', $uidArr)->where('status', 2)
                ->whereIn('auth_code',['bank','alipay'])->get()->toArray();
            $userAuthTwo = AuthRecordModel::whereIn('uid', $uidArr)->where('status', 1)
                ->whereIn('auth_code',['realname','enterprise'])->get()->toArray();
            $userAuth = array_merge($userAuthOne,$userAuthTwo);
        }else{
            $userAuth = array();
        }
        $newUserAuth = array_reduce($userAuth,function(&$newUserAuth,$v){
            $newUserAuth[$v['uid']][] = $v['auth_code'];
            return $newUserAuth;
        });
        if(!empty($newUserAuth)){
            foreach($userArr as $k => $v){
                foreach($newUserAuth as $k1 => $v1){
                    if($v['uid'] == $k1){
                        if(in_array('enterprise',$v1)){
                            $userArr[$k]['isEnterprise'] = 1;
                        }else{
                            $userArr[$k]['isEnterprise'] = 0;
                        }
                        if(in_array('bank',$v1)){
                            $userArr[$k]['bank'] = 1;
                        }else{
                            $userArr[$k]['bank'] = 0;
                        }
                        if(in_array('alipay',$v1)){
                            $userArr[$k]['alipay'] = 1;
                        }else{
                            $userArr[$k]['alipay'] = 0;
                        }
                        if(in_array('realname',$v1)){
                            $userArr[$k]['realname'] = 1;
                        }else{
                            $userArr[$k]['realname'] = 0;
                        }

                    }
                }
            }
        }else{
            foreach($userArr as $k => $v){
                $userArr[$k]['isEnterprise'] = 0;
                $userArr[$k]['bank'] = 0;
                $userArr[$k]['alipay'] = 0;
                $userArr[$k]['realname'] = 0;
            }
        }
        return $userArr;
    }

    public function dealShopArr($shopArr,$domain)
    {
        


        
        $shop_ids = array_pluck($shopArr,'id');
        $uidArr = array_pluck($shopArr,'uid');
        $shopInfoTags = ShopTagsModel::whereIn('shop_id',$shop_ids)->get()->toArray();
        if(!empty($shopInfoTags)){

            
            $tagIds = array_unique(array_pluck($shopInfoTags,'tag_id'));
            
            $tags = SkillTagsModel::whereIn('id',$tagIds)->select('id','tag_name')->get()->toArray();
            $tagsArr = [];
            foreach($tags as $key=>$value) {
                $tagsArr[$value['id']] = $value['tag_name'];
            }
            $shopInfoTags = collect($shopInfoTags)->groupBy('shop_id')->toArray();
            $shopInfoDetail = [];
            foreach($shopInfoTags as $key=>$value) {
                foreach($value as $k=>$v){
                    $shopInfoDetail[$key][] = isset($tagsArr[$v['tag_id']])?$tagsArr[$v['tag_id']]:0;
                }
            }
        }

        
        $provinceInfo = ShopModel::join('district', 'shop.province', '=', 'district.id')
            ->select('shop.id','district.name')
            ->whereIn('shop.id', $shop_ids)
            ->where('shop.status',1)
            ->get()->toArray();
        $cityInfo = ShopModel::join('district', 'shop.city', '=', 'district.id')
            ->select('shop.id','district.name')
            ->whereIn('shop.id', $shop_ids)
            ->where('shop.status',1)
            ->get()->toArray();
        $provinceInfo = collect($provinceInfo)->pluck('name','id')->all();
        $cityInfo = collect($cityInfo)->pluck('name','id')->all();

        foreach($shopArr as $k=>$v){
            $shopArr[$k]['shop_pic'] = $v['shop_pic']?$domain->rule.'/'.$v['shop_pic']:$v['shop_pic'];
            if(isset($provinceInfo[$v['id']])){
                $cityeName = $provinceInfo[$v['id']];
            }else{
                $cityeName = '';
            }
            if(isset($cityInfo[$v['id']])){
                $cityeName.=$cityInfo[$v['id']];
            }else{
                $cityeName.='';
            }

            $shopArr[$k]['city_name'] = $cityeName;
            $shopArr[$k]['cate_name'] = isset($shopInfoDetail[$v['id']])?$shopInfoDetail[$v['id']]:null;
            $shopArr[$k]['total_comment'] = $v['total_comment']?$v['total_comment']:0;
            $shopArr[$k]['good_comment'] = $v['good_comment']?$v['good_comment']:0;

            if(isset( $shopArr[$k]['total_comment']) &&  $shopArr[$k]['total_comment'] > 0){
                $shopArr[$k]['percent'] = intval(( $shopArr[$k]['good_comment'] / $shopArr[$k]['total_comment']*100));
            }
            else{
                $shopArr[$k]['percent'] = 100;
            }

        }

        if(!empty($uidArr)){
            
            $userAuthOne = AuthRecordModel::whereIn('uid', $uidArr)->where('status', 2)
                ->whereIn('auth_code',['bank','alipay'])->get()->toArray();
            $userAuthTwo = AuthRecordModel::whereIn('uid', $uidArr)->where('status', 1)
                ->whereIn('auth_code',['realname','enterprise'])->get()->toArray();
            $emailAuth = UserModel::whereIn('id',$uidArr)->where('email_status', 2)
                ->select('id as uid','email_status')->get()->toArray();
            $userAuth = array_merge($userAuthOne,$userAuthTwo,$emailAuth);
        }else{
            $userAuth = array();
        }
        $newUserAuth = array_reduce($userAuth,function(&$newUserAuth,$v){
            if(isset($v['email_status'])){
                $newUserAuth[$v['uid']][] = 'email';
            }else{
                $newUserAuth[$v['uid']][] = $v['auth_code'];
            }
            return $newUserAuth;
        });
        if(!empty($newUserAuth)){
            foreach($shopArr as $k => $v){
                foreach($newUserAuth as $k1 => $v1){
                    if($v['uid'] == $k1){
                        if(in_array('enterprise',$v1)){
                            $shopArr[$k]['isEnterprise'] = 1;
                        }else{
                            $shopArr[$k]['isEnterprise'] = 0;
                        }
                        if(in_array('bank',$v1)){
                            $shopArr[$k]['bank'] = 1;
                        }else{
                            $shopArr[$k]['bank'] = 0;
                        }
                        if(in_array('alipay',$v1)){
                            $shopArr[$k]['alipay'] = 1;
                        }else{
                            $shopArr[$k]['alipay'] = 0;
                        }
                        if(in_array('email',$v1)){
                            $shopArr[$k]['email'] = 1;
                        }else{
                            $shopArr[$k]['email'] = 0;
                        }
                        if(in_array('realname',$v1)){
                            $shopArr[$k]['realname'] = 1;
                        }else{
                            $shopArr[$k]['realname'] = 0;
                        }

                    }
                }
            }
        }else{
            foreach($shopArr as $k => $v){
                $shopArr[$k]['isEnterprise'] = 0;
                $shopArr[$k]['bank'] = 0;
                $shopArr[$k]['alipay'] = 0;
                $shopArr[$k]['email'] = 0;
                $shopArr[$k]['realname'] = 0;
            }
        }
        return $shopArr;
    }


    
    public function commodityList(Request $request){
        $type = $request->get('type') ? $request->get('type') : 1;
        $name = $request->get('name');
        $cate_id = $request->get('cate_id');
        $cash_order = $request->get('cash_order');
        $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
        $shopList = GoodsModel::where(['status' => 1,'is_delete' => 0,'type' => $type]);
        if($name){
            $shopList = $shopList->where('title','like','%'.$name.'%');
        }
        if($cate_id){
            $shopList = $shopList->where('cate_id',$cate_id);
        }
        if($request->get('desc')){
            switch($request->get('desc')){
                case 1:
                    $shopList = $shopList->orderBy('created_at','desc');
                    break;
                case 2:
                    $shopList = $shopList->orderBy('cash','desc');
                    break;
                case 3:
                    $shopList = $shopList->orderBy('sales_num','desc');
                    break;
                case 4:
                    $shopList = $shopList->orderBy('good_comment','desc');
                    break;
                default:
                    $shopList = $shopList->orderBy('created_at','desc');
            }
        }else{
            
            $shopList = $shopList->orderBy('created_at','desc');
        }
        if($cash_order){
            if($cash_order == 1){
                $cash_order = 'desc';
            }else{
                $cash_order = 'asc';
            }
            $shopList = $shopList->orderBy('cash',$cash_order);
        }
        $shopList = $shopList->select('id','uid','shop_id','cate_id','title','unit','cash','cover','sales_num','comments_num','good_comment')->paginate()->toArray();
        if($shopList['data']){
            
            $shopList['data'] = $this->dealGoodsArr($shopList['data'],$domain);
        }
        if(empty($shopList['data'])){
            
            $new = GoodsModel::where(['status' => 1,'is_delete' => 0,'type' => $type])->select('id','uid','shop_id','cate_id','title','unit','cash','cover','sales_num','comments_num','good_comment')->limit(5)->orderBy('created_at','desc')->get()->toArray();
            $shopList['recommend'] = $this->dealGoodsArr($new,$domain);
        }
        return $this->formateResponse(1000,'获取威客商城信息成功',$shopList);

    }

    
    public function dealGoodsArr($goodsArr,$domain)
    {
        $shop_ids = array_pluck($goodsArr,'shop_id');
        $cate_ids = array_pluck($goodsArr,'cate_id');
        $cateInfo = TaskCateModel::whereIn('id',$cate_ids)->select('id','name')->get()->toArray();
        $cateInfo = collect($cateInfo)->pluck('name','id')->all();
        $provinceInfo = ShopModel::join('district', 'shop.province', '=', 'district.id')
            ->select('shop.id','district.name')
            ->whereIn('shop.id', $shop_ids)
            ->where('shop.status',1)
            ->get()->toArray();
        $cityInfo = ShopModel::join('district', 'shop.city', '=', 'district.id')
            ->select('shop.id','district.name')
            ->whereIn('shop.id', $shop_ids)
            ->where('shop.status',1)
            ->get()->toArray();
        $provinceInfo = collect($provinceInfo)->pluck('name','id')->all();
        $cityInfo = collect($cityInfo)->pluck('name','id')->all();
        foreach($goodsArr as $k=>$v){
            $goodsArr[$k]['cover'] = $v['cover']?$domain->rule.'/'.$v['cover']:$v['cover'];
            $province = (isset($provinceInfo[$v['shop_id']]))? $provinceInfo[$v['shop_id']]:'';
            $city = (isset($cityInfo[$v['shop_id']]))? $cityInfo[$v['shop_id']]:'';
            $goodsArr[$k]['city_name'] = $province.$city;
            $goodsArr[$k]['cate_name'] = isset($cateInfo[$v['cate_id']])?$cateInfo[$v['cate_id']]:null;
            switch($v['unit']){
                case '0':
                    $goodsArr[$k]['unit'] = '件';
                    break;
                case '1':
                    $goodsArr[$k]['unit'] = '时';
                    break;
                case '2':
                    $goodsArr[$k]['unit'] = '份';
                    break;
                case '3':
                    $goodsArr[$k]['unit'] = '个';
                    break;
                case '4':
                    $goodsArr[$k]['unit'] = '张';
                    break;
                case '5':
                    $goodsArr['unit'] = '套';
                    break;
            }
            $goodsArr[$k]['percent'] = 100;
            if($v['comments_num'] > 0){
                $goodsArr[$k]['percent'] = intval($v['good_comment']/$v['comments_num']*100);
            }
        }
        return $goodsArr;
    }

    
    public function getShop(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
        $uid = $tokenInfo['uid'];
        
        $realName = RealnameAuthModel::where('uid',$uid)->where('status',1)->first();
        if(empty($realName)){
            return $this->formateResponse(1001,'请先进行实名认证');
        }
        
        $companyInfo = EnterpriseAuthModel::where('uid', $uid)->orderBy('created_at', 'desc')->first();
        if (isset($companyInfo->status)) {
            switch ($companyInfo->status) {
                case 0:
                    $companyAuth = 2;
                    break;
                case 1:
                    $companyAuth = 1;
                    break;
                case 2:
                    $companyAuth = 3;
                    
                    DB::transaction(function () use ($uid){
                        EnterpriseAuthModel::where('uid', $uid)->delete();
                        AuthRecordModel::where('auth_code', 'enterprise')->where('uid', $uid)->delete();
                    });
                    break;
            }
        }else{
            $companyAuth = 0;
        }

        
        $shopInfo = ShopModel::where('uid',$uid)->first();
        if(!empty($shopInfo)){
            $domain = \CommonClass::getDomain();
            $shopInfo = array_except($shopInfo,array('type','created_at','updated_at','total_comment','good_comment','seo_title',
                'seo_keyword','seo_desc','is_recommend','nav_rules','nav_color','banner_rules','central_ad','footer_ad','shop_bg'));
            
            $province = DistrictModel::where('id',$shopInfo['province'])->select('name')->first();
            $city = DistrictModel::where('id',$shopInfo['city'])->select('name')->first();
            if(!empty($province)){
                $province = $province->name;
            }else{
                $province = '';
            }
            if(!empty($city)){
                $city = $city->name;
            }else{
                $city = '';
            }
            $shopInfoTags = ShopTagsModel::where('shop_id',$shopInfo->id)->get()->toArray();
            if(!empty($shopInfoTags)){
                $tagIds = array();
                foreach($shopInfoTags as $key => $val){
                    $tagIds[] = $val['tag_id'];
                }
                
                $tags = SkillTagsModel::whereIn('id',$tagIds)->select('tag_name')->get()->toArray();
            }else{
                $tags = array();
            }
            $shopInfo['cate_name'] = array_flatten($tags);
            $shopInfo['city_name'] = $province.$city;
            $shopInfo['shop_pic'] = $domain.'/'.$shopInfo['shop_pic'];
            $shopInfo['type'] = $companyAuth == 1 ? '企业店铺' : '个人店铺';

        }
        $data = array(
            'shop_info'       => $shopInfo,
            'is_company_auth' => $companyAuth
        );
        return $this->formateResponse(1000,'获取店铺设置信息信息成功',$data);
    }

    
    public function getShopSkill(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
        $uid = $tokenInfo['uid'];
        
        $hotTag = TagsModel::findAll();
        
        $shop = ShopModel::where('uid',$uid)->first();
        if(!empty($shop)){
            
            $shopInfoTags = ShopTagsModel::where('shop_id',$shop->id)->get()->toArray();
            if(!empty($shopInfoTags)){
                $tagIds = array();
                foreach($shopInfoTags as $key => $val){
                    $tagIds[] = $val['tag_id'];
                }
                
                $tags = SkillTagsModel::whereIn('id',$tagIds)->get()->toArray();
            }else{
                $tags = array();
            }
            $data = array(
                'all_tag' => $hotTag,
                'tags'    => $tags
            );
        }else{
            $data = array(
                'all_tag' => $hotTag,
                'tags'    => array()
            );
        }
        return $this->formateResponse(1000,'获取店铺标签成功',$data);
    }

    
    public function postShopInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_name' => 'required|min:2|max:10',
            'shop_desc' => 'required',

        ],[
            'shop_name.required' => '请输入店铺名称',
            'shop_name.min' => '店铺名称最少2个字符',
            'shop_name.max' => '店铺名称最多10个字符',
            'shop_desc.required' => '请输入店铺介绍',
        ]);
        $error = $validator->errors()->all();
        if(count($error)){
            return $this->formateResponse(1003,$error[0]);
        }
        $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
        $uid = $tokenInfo['uid'];
        $data['uid'] = $uid;
        $data['type'] = 1;
        $data['shop_name'] = $request->get('shop_name') ? $request->get('shop_name') : '';
        $data['shop_desc'] = $request->get('shop_desc') ? $request->get('shop_desc') : '';
        $data['province'] = $request->get('province') ? $request->get('province') : '';
        $data['city'] = $request->get('city') ? $request->get('city') : '';
        $data['tags'] = $request->get('tags') ? $request->get('tags') : '';
        
        $shop = ShopModel::where('uid',$uid)->first();
        if(!empty($shop) && !$request->get('id')){
            return $this->formateResponse(1002,'参数缺少', '编辑是缺少店铺id');
        }
        if($request->get('id') && $request->get('id') != ''){
            
            $data['id'] = $request->get('id');
            $shop = ShopModel::where('id',$data['id'])->first();
            $file = $request->file('shop_pic');
            if ($file) {
                $result = \FileClass::uploadFile($file, 'user');
                $result = json_decode($result, true);
                $data['shop_pic'] = $result['data']['url'];
            }else{
                $data['shop_pic'] = $shop->shop_pic;
            }
            $data['province'] = $request->get('province') ?  $request->get('province') : $shop->province;
            $data['city'] = $request->get('city') ?  $request->get('city') : $shop->city;
            $res = ShopModel::updateShopInfo($data);
        }else{
            
            $file = $request->file('shop_pic');
            if ($file) {
                $result = \FileClass::uploadFile($file, 'user');
                $result = json_decode($result, true);
                $data['shop_pic'] = $result['data']['url'];
            }else{
                $data['shop_pic'] = '';
            }
            $res = ShopModel::createShopInfo($data);
        }
        if($res){
            return $this->formateResponse(1000,'保存成功');
        }else{
            return $this->formateResponse(1001,'保存失败');
        }


    }


    
    public function myShop(Request $request){
        $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
        $uid = $tokenInfo['uid'];
        
        $shopInfo = ShopModel::where('uid',$uid)
            ->select('id','status','uid','shop_pic','shop_desc','shop_name','shop_bg','province','city','total_comment','good_comment')->first();
        if(empty($shopInfo)){
            
            $realName = RealnameAuthModel::where('uid',$uid)->where('status',1)->first();
            if(empty($realName)){
                return $this->formateResponse(1001,'请先进行实名认证');
            }
            return $this->formateResponse(1002,'请先进行店铺设置');
        }
        $shopId = $shopInfo->id;
        $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
        $shopInfo->shop_pic = $shopInfo->shop_pic?$domain->rule.'/'.$shopInfo->shop_pic:$shopInfo->shop_pic;
        $shopInfo->shop_bg = $shopInfo->shop_bg?$domain->rule.'/'.$shopInfo->shop_bg:$shopInfo->shop_bg;
        $shopInfo->cate_name = [];
        $shopTags = ShopTagsModel::where('shop_id',$shopId)->select('tag_id')->get()->toArray();
        if(!empty($shopTags)){
            $tagIds = array_unique(array_flatten($shopTags));
            $tags = SkillTagsModel::whereIn('id',$tagIds)->select('tag_name')->get()->toArray();
            if(!empty($tags)){
                $shopInfo->cate_name = array_values(array_unique(array_flatten($tags)));
            }
        }
        
        if($shopInfo->province){
            $province = DistrictModel::where('id',$shopInfo->province)->select('id','name')->first();
            $provinceName = $province->name;
        }else{
            $provinceName = '';
        }
        if($shopInfo->city){
            $city = DistrictModel::where('id',$shopInfo->city)->select('id','name')->first();
            $cityName = $city->name;
        }else{
            $cityName = '';
        }
        $shopInfo->city_name = $provinceName.$cityName;
        
        $shopInfo['shop_desc'] = htmlspecialchars_decode($shopInfo['shop_desc']);
        
        if(!empty($shopInfo->total_comment)){
            $shopInfo->good_comment_rate = intval($shopInfo->good_comment/$shopInfo->total_comment*100);
        }else{
            $shopInfo->good_comment_rate = 100;
        }
        
        
        
        
        $shopInfo['sale_goods_num'] = GoodsModel::where(['shop_id' => $shopId, 'type' => 1])->select('id')->sum('sales_num');
        
        $shopInfo['sale_service_num'] = GoodsModel::where(['shop_id' => $shopId, 'type' => 2])->select('id')->sum('sales_num');
        
        $employNum = UserDetailModel::where('uid',$uid)->select('employee_num')->first();
        if(!empty($employNum)){
            $employNum = $employNum->employee_num;
        }else{
            $employNum = 0;
        }
        $shopInfo['employ_num'] = (($employNum - $shopInfo['service_num'])> 0) ? $employNum - $shopInfo['service_num'] : 0;

        
        $shopInfo['goods_num'] = GoodsModel::where('shop_id',$shopId)->where('type',1)->where('is_delete',0)->where('status',1)->count();
        
        $shopInfo['service_num'] = GoodsModel::where('shop_id',$shopId)->where('type',2)->where('is_delete',0)->where('status',1)->count();
        
        $shopInfo['success_case_num'] = SuccessCaseModel::where('uid',$shopInfo['uid'])->count();
        
        $goods = GoodsModel::select('id')->where('shop_id',$shopId)->where('type',1)->get()->toArray();
        $goodsId = array_flatten($goods);
        
        $comment1 = GoodsCommentModel::whereIn('goods_id',$goodsId)->count();
        
        $employId = EmployModel::where('employee_uid',$shopInfo['uid'])->lists('id')->toArray();
        $comment2  = EmployCommentsModel::whereIn('employ_id',$employId)->where('to_uid',$shopInfo['uid'])->count();
        $shopInfo['comment_num'] = $comment1 + $comment2;

        
        $userAuthOne = AuthRecordModel::where('uid', $shopInfo->uid)->where('status', 2)->whereIn('auth_code',['bank','alipay'])->get()->toArray();
        $userAuthTwo = AuthRecordModel::where('uid', $shopInfo->uid)->where('status', 1) ->whereIn('auth_code',['realname','enterprise'])->get()->toArray();
        $emailAuthArr = UserModel::where('id', $shopInfo->uid)->select('id as uid','email_status','name')->get()->toArray();
        $shopInfo['username'] = '';
        $emailAuth = [];
        if(!empty($emailAuthArr)){
            $shopInfo['username'] = isset($emailAuthArr[0]['name']) ? $emailAuthArr[0]['name'] : '';
            if(isset($emailAuthArr[0]['email_status']) && $emailAuthArr[0]['email_status'] == 2){
                $emailAuth = $emailAuthArr;
            }
        }
        $userAuth = array_merge($userAuthOne,$userAuthTwo,$emailAuth);
        $auth = array_reduce($userAuth,function(&$auth,$v){
            if(isset($v['email_status'])){
                $auth[] = 'email';
            }else{
                $auth[] = $v['auth_code'];
            }
            return $auth;
        });
        if(!empty($auth)){

            if(in_array('enterprise',$auth)){
                $shopInfo['isEnterprise'] = 1;
            }else{
                $shopInfo['isEnterprise'] = 0;
            }
            if(in_array('bank',$auth)){
                $shopInfo['bank'] = 1;
            }else{
                $shopInfo['bank'] = 0;
            }
            if(in_array('alipay',$auth)){
                $shopInfo['alipay'] = 1;
            }else{
                $shopInfo['alipay'] = 0;
            }
            if(in_array('email',$auth)){
                $shopInfo['email'] = 1;
            }else{
                $shopInfo['email'] = 0;
            }
            if(in_array('realname',$auth)){
                $shopInfo['realname'] = 1;
            }else{
                $shopInfo['realname'] = 0;
            }

        } 
        $userAuthOne = AuthRecordModel::where('uid', $shopInfo->uid)->where('status', 2)->whereIn('auth_code',['bank','alipay'])->get()->toArray();
        $userAuthTwo = AuthRecordModel::where('uid', $shopInfo->uid)->where('status', 1) ->whereIn('auth_code',['realname','enterprise'])->get()->toArray();
        $emailAuthArr = UserModel::where('id', $shopInfo->uid)->select('id as uid','email_status','name')->get()->toArray();
        $shopInfo['username'] = '';
        $emailAuth = [];
        if(!empty($emailAuthArr)){
            $shopInfo['username'] = isset($emailAuthArr[0]['name']) ? $emailAuthArr[0]['name'] : '';
            if(isset($emailAuthArr[0]['email_status']) && $emailAuthArr[0]['email_status'] == 2){
                $emailAuth = $emailAuthArr;
            }
        }
        $userAuth = array_merge($userAuthOne,$userAuthTwo,$emailAuth);
        $auth = array_reduce($userAuth,function(&$auth,$v){
            if(isset($v['email_status'])){
                $auth[] = 'email';
            }else{
                $auth[] = $v['auth_code'];
            }
            return $auth;
        });
        if(!empty($auth)){

            if(in_array('enterprise',$auth)){
                $shopInfo['isEnterprise'] = 1;
            }else{
                $shopInfo['isEnterprise'] = 0;
            }
            if(in_array('bank',$auth)){
                $shopInfo['bank'] = 1;
            }else{
                $shopInfo['bank'] = 0;
            }
            if(in_array('alipay',$auth)){
                $shopInfo['alipay'] = 1;
            }else{
                $shopInfo['alipay'] = 0;
            }
            if(in_array('email',$auth)){
                $shopInfo['email'] = 1;
            }else{
                $shopInfo['email'] = 0;
            }
            if(in_array('realname',$auth)){
                $shopInfo['realname'] = 1;
            }else{
                $shopInfo['realname'] = 0;
            }

        }
        return $this->formateResponse(1000,'获取我的店铺信息成功',$shopInfo);


    }


    
    public function shopDetail(Request $request){

        if(!$request->get('shop_id')){
            return $this->formateResponse(1001,'缺少参数');
        }
        $shopId = $request->get('shop_id');
        
        $shopInfo = ShopModel::where('id',$shopId)
            ->select('id','uid','status','shop_pic','shop_desc','shop_name','shop_bg','province','city','total_comment','good_comment')->first();
        if(!empty($shopInfo)){
            $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
            $shopInfo->shop_pic = $shopInfo->shop_pic?$domain->rule.'/'.$shopInfo->shop_pic:$shopInfo->shop_pic;
            $shopInfo->shop_bg = $shopInfo->shop_bg?$domain->rule.'/'.$shopInfo->shop_bg:$shopInfo->shop_bg;
            $shopInfo->cate_name = [];
            $shopTags = ShopTagsModel::where('shop_id',$shopId)->select('tag_id')->get()->toArray();
            if(!empty($shopTags)){
                $tagIds = array_unique(array_flatten($shopTags));
                $tags = SkillTagsModel::whereIn('id',$tagIds)->select('tag_name')->get()->toArray();

                if(!empty($tags)){
                    $tags = array_values(array_unique(array_flatten($tags)));
                    $shopInfo->cate_name = array_unique(array_flatten($tags));
                }
            }
            
            $shopInfo->city_name = DistrictModel::getAreaName($shopInfo->province,$shopInfo->city);

            
            $shopInfo['shop_desc'] = htmlspecialchars_decode($shopInfo['shop_desc']);
            
            $shopInfo->good_comment_rate = 100;
            if(!empty($shopInfo->total_comment)){
                $good_comment_rate = $shopInfo->good_comment/$shopInfo->total_comment;
                if($good_comment_rate){
                    $shopInfo->good_comment_rate =  number_format($good_comment_rate, 1) * 100;
                }

            }

            
           

            
            $goods = GoodsModel::select('id')->where('shop_id',$shopId)->where('type',1)->get()->toArray();
            $goodsId = array_flatten($goods);
            
            
            $goodsCommentAtt = number_format(GoodsCommentModel::whereIn('goods_id',$goodsId)->avg('attitude_score'),1);
            $goodsCommentSpeed = number_format(GoodsCommentModel::whereIn('goods_id',$goodsId)->avg('speed_score'),1);
            $goodsCommentQuality = number_format(GoodsCommentModel::whereIn('goods_id',$goodsId)->avg('quality_score'),1);
            
            
            $employ = EmployModel::where('employee_uid',$shopInfo->uid)->select('id')->get()->toArray();
            $employId = array_flatten($employ);
            $employCommentAtt = number_format(EmployCommentsModel::where('to_uid',$shopInfo->uid)->whereIn('employ_id',$employId)->avg('attitude_score'),1);
            $employCommentSpeed = number_format(EmployCommentsModel::where('to_uid',$shopInfo->uid)->whereIn('employ_id',$employId)->avg('speed_score'),1);
            $employCommentQuality = number_format(EmployCommentsModel::where('to_uid',$shopInfo->uid)->whereIn('employ_id',$employId)->avg('quality_score'),1);
            if(($goodsCommentAtt>0 || $goodsCommentSpeed>0 || $goodsCommentQuality>0) && ($employCommentAtt>0 || $employCommentSpeed>0 || $employCommentQuality>0)){
                $totalScore = $goodsCommentAtt + $employCommentAtt + $goodsCommentSpeed + $employCommentSpeed + $goodsCommentQuality + $employCommentQuality;
                $shopInfo['avg_score'] = number_format($totalScore/6,1);
            }elseif($employCommentAtt>0 || $employCommentSpeed>0 || $employCommentQuality>0){
                $totalScore = $employCommentAtt + $employCommentSpeed  + $employCommentQuality;
                $shopInfo['avg_score'] = number_format($totalScore/3,1);
            }elseif($goodsCommentAtt>0 || $goodsCommentSpeed>0 || $goodsCommentQuality>0){
                $totalScore = $goodsCommentAtt + $goodsCommentSpeed  + $goodsCommentQuality;
                $shopInfo['avg_score'] = number_format($totalScore/3,1);
            }else{
                $shopInfo['avg_score'] = 0;
            }


            
            

            $shopInfo['total_service'] = GoodsModel::where(['shop_id' => $shopId, 'status' => 1])->select('id')->sum('sales_num');
            
            
            $userAuthOne = AuthRecordModel::where('uid', $shopInfo->uid)->where('status', 2)->whereIn('auth_code',['bank','alipay'])->get()->toArray();
            $userAuthTwo = AuthRecordModel::where('uid', $shopInfo->uid)->where('status', 1) ->whereIn('auth_code',['realname','enterprise'])->get()->toArray();
            $emailAuthArr = UserModel::where('id', $shopInfo->uid)->select('id as uid','email_status','name')->get()->toArray();
            $shopInfo['username'] = '';
            $emailAuth = [];
            if(!empty($emailAuthArr)){
                $shopInfo['username'] = isset($emailAuthArr[0]['name']) ? $emailAuthArr[0]['name'] : '';
                if(isset($emailAuthArr[0]['email_status']) && $emailAuthArr[0]['email_status'] == 2){
                    $emailAuth = $emailAuthArr;
                }
            }
            $userAuth = array_merge($userAuthOne,$userAuthTwo,$emailAuth);
            $auth = array_reduce($userAuth,function(&$auth,$v){
                if(isset($v['email_status'])){
                    $auth[] = 'email';
                }else{
                    $auth[] = $v['auth_code'];
                }
                return $auth;
            });
            if(!empty($auth)){

                if(in_array('enterprise',$auth)){
                    $shopInfo['isEnterprise'] = 1;
                }else{
                    $shopInfo['isEnterprise'] = 0;
                }
                if(in_array('bank',$auth)){
                    $shopInfo['bank'] = 1;
                }else{
                    $shopInfo['bank'] = 0;
                }
                if(in_array('alipay',$auth)){
                    $shopInfo['alipay'] = 1;
                }else{
                    $shopInfo['alipay'] = 0;
                }
                if(in_array('email',$auth)){
                    $shopInfo['email'] = 1;
                }else{
                    $shopInfo['email'] = 0;
                }
                if(in_array('realname',$auth)){
                    $shopInfo['realname'] = 1;
                }else{
                    $shopInfo['realname'] = 0;
                }

            }
            
            $shopInfo['goods_num'] = GoodsModel::where('shop_id',$shopId)->where('type',1)->where('is_delete',0)->where('status',1)->count();
            
            $shopInfo['service_num'] = GoodsModel::where('shop_id',$shopId)->where('type',2)->where('is_delete',0)->where('status',1)->count();
            
            $shopInfo['success_case_num'] = SuccessCaseModel::where('uid',$shopInfo['uid'])->count();
            
            $comment1 = GoodsCommentModel::whereIn('goods_id',$goodsId)->count();
            $comment2  = EmployCommentsModel::whereIn('employ_id',$employId)->where('to_uid',$shopInfo['uid'])->count();
            $shopInfo['comment_num'] = $comment1 + $comment2;
            return $this->formateResponse(1000,'获取威客店铺信息成功',$shopInfo);
        }else{
            return $this->formateResponse(1002,'参数有误');
        }

    }

    
    public function userDetail(Request $request){

        if(!$request->get('uid')){
            return $this->formateResponse(1001,'缺少参数');
        }
        $uid = $request->get('uid');
        
        $userInfo = UserModel::where('users.id',$uid)
            ->select('users.id','users.name','user_detail.avatar','user_detail.province','user_detail.city','user_detail.introduce','user_detail.employee_num as service_num','users.email_status')->leftJoin('user_detail','user_detail.uid','=','users.id')->first();
        if(!empty($userInfo)){
            $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
            $userInfo->avatar = $userInfo->avatar?$domain->rule.'/'.$userInfo->avatar:$userInfo->avatar;
            $userInfo->cate_name = [];
            $userTags = UserTagsModel::where('uid',$uid)->select('tag_id')->get()->toArray();
            if(!empty($userTags)){
                $tagIds = array_unique(array_flatten($userTags));
                $tags = SkillTagsModel::whereIn('id',$tagIds)->select('tag_name')->get()->toArray();

                if(!empty($tags)){
                    $tags = array_values(array_unique(array_flatten($tags)));
                    $userInfo->cate_name = array_unique(array_flatten($tags));
                }
            }
            
            $userInfo->city_name = DistrictModel::getAreaName($userInfo->province,$userInfo->city);

            
            $goodsComment = CommentModel::where('to_uid',$uid)->where('type',1)->count();
            $comment = CommentModel::where('to_uid',$uid)->count();
            $userInfo->good_comment = $goodsComment;
            $userInfo->total_comment = $comment;
            
            if($comment > 0){
                $userInfo->good_comment_rate = intval($goodsComment/$comment*100);
            }else{
                $userInfo->good_comment_rate = 100;
            }

            
            
            $taskCommentAtt = number_format(CommentModel::where('to_uid',$uid)->avg('attitude_score'),1);
            $taskCommentSpeed = number_format(CommentModel::where('to_uid',$uid)->avg('speed_score'),1);
            $taskCommentQuality = number_format(CommentModel::where('to_uid',$uid)->avg('quality_score'),1);
            
           

            $totalScore = $taskCommentAtt + $taskCommentSpeed + $taskCommentQuality;
            $userInfo['avg_score'] = number_format($totalScore/3,1);

            
            $userAuthOne = AuthRecordModel::where('uid', $uid)->where('status', 2)->whereIn('auth_code',['bank','alipay'])->get()->toArray();
            $userAuthTwo = AuthRecordModel::where('uid', $uid)->where('status', 1) ->whereIn('auth_code',['realname','enterprise'])->get()->toArray();

            $userAuth = array_merge($userAuthOne,$userAuthTwo);
            $auth = array_reduce($userAuth,function(&$auth,$v){
                if(isset($v['email_status'])){
                    $auth[] = 'email';
                }else{
                    $auth[] = $v['auth_code'];
                }
                return $auth;
            });
            if(!empty($auth)){

                if(in_array('enterprise',$auth)){
                    $userInfo['isEnterprise'] = 1;
                }else{
                    $userInfo['isEnterprise'] = 0;
                }
                if(in_array('bank',$auth)){
                    $userInfo['bank'] = 1;
                }else{
                    $userInfo['bank'] = 0;
                }
                if(in_array('alipay',$auth)){
                    $userInfo['alipay'] = 1;
                }else{
                    $userInfo['alipay'] = 0;
                }
                if(in_array('realname',$auth)){
                    $userInfo['realname'] = 1;
                }else{
                    $userInfo['realname'] = 0;
                }

            }
            if($userInfo['email_status'] == 2){
                $userInfo['email'] = 1;
                unset($userInfo['email_status']);
            }else{
                $userInfo['email'] = 0;
            }
            
            $userInfo['comment_num'] = CommentModel::where('to_uid',$uid)->count();

            return $this->formateResponse(1000,'获取威客用户信息成功',$userInfo);
        }else{
            return $this->formateResponse(1002,'参数有误');
        }

    }

    
    public function taskCommentList(Request $request)
    {
        $uid = $request->get('uid');
        $type = $request->get('type') ? $request->get('type') : 0;
        $userInfo = UserModel::where(['id' => $uid])->first();
        if(empty($userInfo)){
            return $this->formateResponse(1016,'传送参数错误');
        }
        $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
        $commentList = CommentModel::where('comments.to_uid', $uid)
            ->join('task', 'comments.task_id', '=', 'task.id')
            ->join('user_detail', 'comments.from_uid', '=', 'user_detail.uid')
            ->leftJoin('users','users.id','=','comments.from_uid')
            ->select('users.name','user_detail.avatar','task.title','task.bounty','comments.comment','comments.type','comments.speed_score','comments.quality_score','comments.attitude_score','comments.created_at');
        switch($type){
            case 1:
                $commentList = $commentList->where('type',1);
                break;
            case 2:
                $commentList = $commentList->where('type',2);
                break;
            case 3:
                $commentList = $commentList->where('type',3);
                break;
        }
        $comment = $commentList->paginate(2)->toArray();

        if($comment['data']){
            foreach($comment['data'] as $k => $v){
                $comment['data'][$k]['avatar'] = $v['avatar']?$domain->rule.'/'.$v['avatar']:$v['avatar'];
                if($v['type'] == 1){
                    $comment['data'][$k]['type'] = '好评';
                }
                elseif($v['type'] == 2){
                    $comment['data'][$k]['type'] = '中评';
                }
                elseif($v['type'] == 3){
                    $comment['data'][$k]['type'] = '差评';
                }
                if(!empty($v['attitude_score'])){
                    $comment['data'][$k]['avg_score'] = number_format(($v['speed_score'] + $v['quality_score'] +$v['attitude_score'])/3,1);
                }else{
                    $comment['data'][$k]['avg_score'] = number_format(($v['speed_score'] + $v['quality_score'])/2,1);
                }
                $comment['data'][$k]['desc'] = $v['bounty'].'元/'.$v['title'];
                $comment['data'][$k]['created_at'] = date('Y年m月d日',strtotime($v['created_at']));
                unset($comment['data'][$k]['speed_score'],$comment['data'][$k]['quality_score'],$comment['data'][$k]['attitude_score'],$comment['data'][$k]['title'],$comment['data'][$k]['bounty']);
            }
        }

        
        $taskCommentAtt = number_format(CommentModel::where('to_uid',$uid)->avg('attitude_score'),1);
        $taskCommentSpeed = number_format(CommentModel::where('to_uid',$uid)->avg('speed_score'),1);
        $taskCommentQuality = number_format(CommentModel::where('to_uid',$uid)->avg('quality_score'),1);
        $totalScore = $taskCommentAtt + $taskCommentSpeed + $taskCommentQuality;
        $comment['attitude_score'] = number_format($taskCommentAtt,1);
        $comment['speed_score'] = number_format($taskCommentSpeed,1);
        $comment['quality_score'] = number_format($taskCommentQuality,1);
        $comment['avg_score'] = number_format($totalScore/3,1);

        return $this->formateResponse(1000,'获取服务商任务评价信息成功',$comment);

    }

    
    public function saveShopBg(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
        $uid = $tokenInfo['uid'];
        
        $shop = ShopModel::where('uid',$uid)->first();
        if(!empty($shop)){
            
            $file = $request->file('shop_bg');
            if ($file) {
                $result = \FileClass::uploadFile($file, 'user');
                $result = json_decode($result, true);
                $data['shop_bg'] = $result['data']['url'];
            }else{
                return $this->formateResponse(1002,'缺少参数');
            }
            $res = ShopModel::where('uid',$uid)->update($data);
            if($res){
                return $this->formateResponse(1000,'保存成功');
            }else{
                return $this->formateResponse(1001,'保存失败');
            }
        }else{
            return $this->formateResponse(1003,'店铺不存在');
        }
    }


    
    public function changeShopStatus(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
        $uid = $tokenInfo['uid'];
        $shopInfo = ShopModel::where('uid', $uid)->first();
        $data = [
            'uid' => $uid,
            'shopId' => $shopInfo->id
        ];
        if ($shopInfo['status'] == 1) {
            
            $res = DB::transaction(function () use ($data) {
                ShopModel::where('id', $data['shopId'])->update(['status' => 2, 'updated_at' => date('Y-m-d H:i:s', time())]);
                UserDetailModel::where('uid', $data['uid'])->update(['shop_status' => 2, 'updated_at' => date('Y-m-d H:i:s', time())]);
                $auditInfo = GoodsModel::where(['shop_id' => $data['shopId'], 'status' => 0])->get();
                if (!empty($auditInfo)) {
                    GoodsModel::where(['shop_id' => $data['shopId'], 'status' => 0])->update(['status' => 3]);
                }
                $salesInfo = GoodsModel::where(['shop_id' => $data['shopId'], 'status' => 1])->get();
                if (!empty($salesInfo)) {
                    GoodsModel::where(['shop_id' => $data['shopId'], 'status' => 1])->update(['status' => 2]);
                }
                return true;
            });
            if($res){
                $info = array(
                    'msg' =>'店铺关闭，商品全部下架'
                );
            }
        } else {
            
            $res = DB::transaction(function () use ($data) {
                ShopModel::where('id', $data['shopId'])->update(['status' => 1, 'updated_at' => date('Y-m-d H:i:s', time())]);
                UserDetailModel::where('uid', $data['uid'])->update(['shop_status' => 1, 'updated_at' => date('Y-m-d H:i:s', time())]);
                return true;
            });
            if($res){
                $info = array(
                    'msg' =>'店铺开启'
                );

            }

        }
        if($res){
            return $this->formateResponse(1000,'保存成功',$info);
        }else{
            return $this->formateResponse(1001,'保存失败');
        }
    }

}