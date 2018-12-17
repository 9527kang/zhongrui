<?php

namespace App\Modules\Advertisement\Model;

use App\Modules\Shop\Models\GoodsModel;
use App\Modules\Shop\Models\ShopModel;
use App\Modules\Shop\Models\ShopTagsModel;
use App\Modules\Task\Model\TaskCateModel;
use App\Modules\User\Model\AuthRecordModel;
use App\Modules\User\Model\CommentModel;
use App\Modules\User\Model\DistrictModel;
use App\Modules\User\Model\SkillTagsModel;
use App\Modules\User\Model\TagsModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use App\Modules\User\Model\UserTagsModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class RePositionModel extends Model
{
    protected $table = 'recommend_position';
    protected $fillable =
        [   'id',
            'name',
            'code',
            'position',
            'num',
            'pic',
            'is_open'
        ];
    public  $timestamps = false;  

    
    static public function getHomeRecommendShop()
    {
        $recommendPositionShop = RePositionModel::where('code','HOME_MIDDLE_SHOP')->where('is_open',1)->first();
        $recommendShop = [];
        if($recommendPositionShop['id']){
            $recommendShop = RecommendModel::getRecommendInfo($recommendPositionShop['id'],'shop')
                ->leftJoin('shop','shop.id','=','recommend.recommend_id')->orderBy('recommend.created_at','DESC')
                ->get()->toArray();
        }
        if(!empty($recommendShop) && is_array($recommendShop)) {
            $recommendIds = array_pluck($recommendShop,'uid');
            $recommendShopIds = array_pluck($recommendShop,'recommend_id');
            $provinceId = array_pluck($recommendShop,'province');
            $cityId = array_pluck($recommendShop,'city');
            $authArr = [];
            $serviceNum = [];
            if(!empty($recommendIds)){
                
                $userAuth = AuthRecordModel::whereIn('uid', $recommendIds)
                    ->where(function($query){
                        $query->where(function($querys){
                            $querys->where('status', 2)->whereIn('auth_code',['bank','alipay']);
                        })->orwhere(function($querys){
                            $querys->where('status', 1)->whereIn('auth_code',['realname','enterprise']);
                        });
                    })->select('uid','auth_code')->get()->toArray();

                $emailAuth = UserModel::whereIn('id',$recommendIds)->select('id as uid','email_status as auth_code')->where('email_status',2)->get()->toArray();
                $authArr = array_merge($userAuth,$emailAuth);
                $authArr = \CommonClass::setArrayKey($authArr,'uid',2,'auth_code');

                
                $serviceNum = GoodsModel::whereIn('shop_id',$recommendShopIds)->where('status',1)->groupBy('shop_id')->get(['shop_id',DB::raw('SUM(sales_num) as value')])->toArray();
                $serviceNum = \CommonClass::setArrayKey($serviceNum,'shop_id');
            }
            $shopGoods = [];
            $district = [];
            if(!empty($recommendShopIds)){
                
                $shopGoods = GoodsModel::whereIn('goods.shop_id',$recommendShopIds)
                    ->where('goods.status',1)
                    ->where('goods.is_delete',0)
                    ->leftJoin('cate','cate.id','=','goods.cate_id')
                    ->select('goods.*','cate.name')
                    ->orderBy('goods.created_at','DESC')
                    ->get()->toArray();
                $shopGoods = \CommonClass::setArrayKey($shopGoods,'uid',2);

                
                $skill = ShopTagsModel::whereIn('shop_id',$recommendShopIds)
                    ->leftJoin('skill_tags','skill_tags.id','=','tag_shop.tag_id')
                    ->select('tag_shop.*','skill_tags.tag_name')
                    ->get()->toArray();
                $sk = \CommonClass::setArrayKey($skill,'shop_id',2,'tag_name');
                
                $districtId = array_merge($provinceId,$cityId);
                $district = DistrictModel::whereIn('id',$districtId)->select('id','name')->get()->toArray();
                $district = \CommonClass::setArrayKey($district,'id');
            }
            foreach($recommendShop as $m => $n)
            {
                if(!empty($shopGoods) && in_array($n['uid'],array_keys($shopGoods))){
                    $recommendShop[$m]['success'] = $shopGoods[$n['uid']];
                }else{
                    $recommendShop[$m]['success'] = [];
                }

                if(!empty($authArr) && in_array($n['uid'],array_keys($authArr))){
                    $recommendShop[$m]['auth'] = $authArr[$n['uid']];
                }else{
                    $recommendShop[$m]['auth'] = [];
                }


                if(!empty($serviceNum) && in_array($n['recommend_id'],array_keys($serviceNum))){
                    $recommendShop[$m]['serviceNum'] = $serviceNum[$n['recommend_id']]['value'];
                }else{
                    $recommendShop[$m]['serviceNum'] = 0;
                }

                if(!empty($sk) && in_array($n['recommend_id'],array_keys($sk))){
                    $recommendShop[$m]['skill_name'] = implode('|',$sk[$n['recommend_id']]);
                }else{
                    $recommendShop[$m]['skill_name'] = '';
                }
                $province = '';
                if(in_array($n['province'],array_keys($district))){
                    $province = $district[$n['province']]['name'];
                }
                $city = '';
                if(in_array($n['city'],array_keys($district))){
                    $city = $district[$n['city']]['name'];
                }
                $recommendShop[$m]['addr'] = $province.$city;
                $recommendShop[$m]['good_comment_rate'] = 100;
                if( !empty($recommendShop[$m]['total_comment'])) {
                    $recommendShop[$m]['good_comment_rate'] =
                        intval(($recommendShop[$m]['good_comment']/ $recommendShop[$m]['total_comment'])*100);
                }

                $recommendShop[$m]['realname_auth']  = false;
                $recommendShop[$m]['bank_auth'] = false;
                $recommendShop[$m]['alipay_auth'] = false;
                $recommendShop[$m]['enterprise_auth']= false;
                if(!empty($recommendShop[$m]['auth']) && is_array($recommendShop[$m]['auth'])) {
                    if (in_array('realname', $recommendShop[$m]['auth'])) {
                        $recommendShop[$m]['realname_auth'] = true;
                    } else {
                        $recommendShop[$m]['realname_auth']  = false;
                    }
                    if (in_array('bank', $recommendShop[$m]['auth'])) {
                        $recommendShop[$m]['bank_auth']  = true;
                    } else {
                        $recommendShop[$m]['bank_auth'] = false;
                    }
                    if (in_array('alipay', $recommendShop[$m]['auth'])) {
                        $recommendShop[$m]['alipay_auth'] = true;
                    } else {
                        $recommendShop[$m]['alipay_auth']= false;
                    }
                    if (in_array('enterprise', $recommendShop[$m]['auth'])) {
                        $recommendShop[$m]['enterprise_auth'] = true;
                    } else {
                        $recommendShop[$m]['enterprise_auth']= false;
                    }
                    if (in_array('2', $recommendShop[$m]['auth'])) {
                        $recommendShop[$m]['email_status'] = 2;
                    } else {
                        $recommendShop[$m]['email_status']= 0;
                    }
                }
            }
        }

        return $data = [
            'recommend_shop' => $recommendPositionShop,
            'shop_before' => $recommendShop,
        ];
    }

    
    static public function getHomeRecommendWork()
    {
        $recommendWork = [];
        $recommendPositionWork = RePositionModel::where('code','HOME_MIDDLE_WORK')->where('is_open',1)->first();
        if($recommendPositionWork['id']){
            $recommendWork = RecommendModel::getRecommendInfo($recommendPositionWork['id'],'work')
                ->join('goods','goods.id','=','recommend.recommend_id')
                ->leftJoin('cate','cate.id','=','goods.cate_id')
                ->select('recommend.*','goods.*','cate.name')
                ->orderBy('recommend.sort','ASC')->orderBy('recommend.created_at','DESC')->get()->toArray();
        }
        return $data = [
            'recommend_work' => $recommendPositionWork,
            'work' => $recommendWork,
        ];
    }

    
    static public function getHomeRecommendService()
    {
        $recommendServer = [];
        $recommendPositionServer = RePositionModel::where('code','HOME_MIDDLE_SERVICE')->where('is_open',1)->first();
        if($recommendPositionServer['id']){
            $recommendServer = RecommendModel::getRecommendInfo($recommendPositionServer['id'],'server')
                ->join('goods','goods.id','=','recommend.recommend_id')
                ->leftJoin('cate','cate.id','=','goods.cate_id')
                ->select('recommend.*','goods.*','cate.name')
                ->orderBy('recommend.sort','ASC')->orderBy('recommend.created_at','DESC')->get()->toArray();
        }
        return $data = [
            'recommend_server' => $recommendPositionServer,
            'server' => $recommendServer,
        ];
    }


    
    static public function getHomeRecommendSuccess()
    {
        $recommendSuccess = [];
        $recommendPositionSuccess = RePositionModel::where('code','HOME_MIDDLE_BOTTOM')->where('is_open',1)->first();
        if($recommendPositionSuccess['id']){
            $recommendSuccess = RecommendModel::getRecommendInfo($recommendPositionSuccess['id'],'successcase')
                ->join('success_case','success_case.id','=','recommend.recommend_id')
                ->leftJoin('cate','cate.id','=','success_case.cate_id')
                ->leftJoin('user_detail','user_detail.uid','=','success_case.uid')
                ->leftJoin('users','users.id','=','success_case.uid')
                ->select('recommend.*','success_case.id','success_case.cate_id','success_case.title','success_case.pic as success_pic','success_case.username','success_case.cash',
                    'cate.name','user_detail.avatar','users.name as bidname')
                ->orderBy('recommend.sort','ASC')->orderBy('recommend.created_at','DESC')->get()->toArray();
        }
        return $data = [
            'success' => $recommendSuccess,
            'recommend_success' =>$recommendPositionSuccess,
        ];
    }

    
    static public function getHomeRecommendArticle()
    {
        $article = [];
        $recommendPositionArticle = RePositionModel::where('code','HOME_BOTTOM')->where('is_open',1)->first();
        if($recommendPositionArticle['id']){
            $article = RecommendModel::getRecommendInfo($recommendPositionArticle['id'],'article')
                ->join('article','article.id','=','recommend.recommend_id')
                ->leftJoin('article_category','article_category.id','=','article.cat_id')
                ->select('recommend.*','article_category.cate_name','article.summary','article.title')
                ->orderBy('recommend.created_at','DESC')->get()->toArray();
        }
        $articleArr = [];
        if(!empty($article) && is_array($article)) {
            foreach($article as $k => $v) {
                if($k > 0) {
                    $articleArr[] = $v;
                }
            }
        }
        return $data = [
            'articleArr'        => $articleArr,
            'article'           => $article,
            'recommend_article' => $recommendPositionArticle,
        ];
    }

    
    static public function getBlackHome()
    {
        $list = UserModel::select('user_detail.sign', 'users.name', 'user_detail.avatar', 'users.id','users.email_status','user_detail.employee_praise_rate','user_detail.shop_status','shop.is_recommend','shop.id as shopId')
            ->leftJoin('user_detail', 'users.id', '=', 'user_detail.uid')
            ->leftJoin('shop','user_detail.uid','=','shop.uid')->where('users.status','<>', 2)
            ->orderBy('shop.is_recommend','DESC')
            ->limit(5)->get()->toArray();
        $arrUid = array_pluck($list,'id');
        
        $commentCount = CommentModel::whereIn('to_uid',$arrUid)->groupBy('to_uid')->get(['to_uid',DB::raw('COUNT(*) as count')])->toArray();
        $commentCount = \CommonClass::setArrayKey($commentCount,'to_uid');
        
        $goodComment = CommentModel::whereIn('to_uid',$arrUid)->where('type',1)->groupBy('to_uid')->get(['to_uid',DB::raw('COUNT(*) as count')])->toArray();
        $goodCommentCount = \CommonClass::setArrayKey($goodComment,'to_uid');
        
        foreach($list as $key => $value){
            $list[$key]['good_comment_count'] = in_array($value['id'],array_keys($goodCommentCount)) ? $goodCommentCount[$value['id']]['count'] : 0;
            $list[$key]['comment_count'] = in_array($value['id'],array_keys($commentCount)) ? $commentCount[$value['id']]['count'] : 0;

            
            $list[$key]['percent'] = 100;
            if($list[$key]['comment_count'] > 0){
                $list[$key]['percent'] = ($list[$key]['good_comment_count']/$list[$key]['comment_count']*100);
            }

        }
        
        $arrSkill = UserTagsModel::getTagsByUserId($arrUid);
        if(!empty($arrSkill) && is_array($arrSkill)){
            $arrTagId = array_pluck($arrSkill,'tag_id');
            $arrTagName = TagsModel::select('id', 'tag_name')->whereIn('id', $arrTagId)->get()->toArray();
            $arrTagName = \CommonClass::setArrayKey($arrTagName,'id');
            $arrUserTag = [];
            foreach ($arrSkill as $item){
                $arrUserTag[$item['uid']][] = !empty($arrTagName) && in_array($item['uid'],array_keys($arrTagName)) ? $arrTagName[$item['uid']]['tag_name']: [];
            }
            foreach ($list as $key => $item){
                $list[$key]['skill'] = in_array($item['id'],array_keys($arrUserTag)) ? $arrUserTag[$item['id']] : [];
            }

            $data['service'] = $list;
        }


        
        $userAuth = AuthRecordModel::whereIn('uid', $arrUid)
            ->where(function($query){
                $query->where(function($querys){
                    $querys->where('status', 2)->whereIn('auth_code',['bank','alipay']);
                })->orwhere(function($querys){
                    $querys->where('status', 1)->whereIn('auth_code',['realname','enterprise']);
                });
            })->select('uid','auth_code')->get()->toArray();

        $emailAuth = UserModel::whereIn('id',$arrUid)->select('id as uid','email_status as auth_code')->where('email_status',2)->get()->toArray();
        $authArr = array_merge($userAuth,$emailAuth);
        $auth = \CommonClass::setArrayKey($authArr,'uid',2,'auth_code');

        if(!empty($auth) && is_array($auth)) {
            foreach ($auth as $e => $f) {
                $auth[$e]['uid'] = $e;
                if (in_array('realname', $f)) {
                    $auth[$e]['realname'] = true;
                } else {
                    $auth[$e]['realname'] = false;
                }
                if (in_array('bank', $f)) {
                    $auth[$e]['bank'] = true;
                } else {
                    $auth[$e]['bank'] = false;
                }
                if (in_array('alipay', $f)) {
                    $auth[$e]['alipay'] = true;
                } else {
                    $auth[$e]['alipay'] = false;
                }
                if (in_array('enterprise', $f)) {
                    $auth[$e]['enterprise'] = true;
                } else {
                    $auth[$e]['enterprise'] = false;
                }
                if (in_array('2', $f)) {
                    $auth[$e]['email_status'] = 2;
                } else {
                    $auth[$e]['email_status'] = false;
                }
            }
            foreach ($list as $key => $item) {
                
                $list[$key]['auth'] = !empty($auth) && in_array($item['id'],array_keys($auth)) ? $auth[$item['id']] : [];
            }
        }

        $goodsInfo = GoodsModel::where('status',1)
            ->select('id','uid','shop_id','title','type','cash','cover','sales_num','good_comment', 'comments_num')
            ->where(function($goodsInfo){
                $goodsInfo->where('is_recommend',0)
                    ->orWhere(function($goodsInfo){
                        $goodsInfo->where('is_recommend',1)->where('recommend_end','>',date('Y-m-d H:i:s',time()));
                    });})
            ->orderBy('is_recommend','desc')->orderBy('created_at','desc')->limit(10)->get()->toArray();

        if (!empty($goodsInfo)){
            $uid = array_pluck($goodsInfo,'uid');

            $cityInfo = ShopModel::join('district', 'shop.city', '=', 'district.id')
                ->select('shop.uid','district.name')->whereIn('shop.uid', $uid)->get()->toArray();
            $cityInfo = \CommonClass::setArrayKey($cityInfo,'uid');
            foreach($goodsInfo as $gk => $gv){
                $goodsInfo[$gk]['addr'] = !empty($cityInfo) && in_array($gv['uid'],array_keys($cityInfo)) ? $cityInfo[$gv['uid']]['name'] : '';

            }
        }
        return $arr = [
            'service' => $list,
            'goods_info' => $goodsInfo
        ];
    }

    
    static public function getZbjHome()
    {
        
        $cate = TaskCateModel::where('pid',0)->orderBy('sort','ASC')->limit(6)->get()->toArray();
        $userArr = [];
        $cateId = '';
        if($cate){
            $cateId = $cate[0]['id'];
            
            $childCate = TaskCateModel::where('pid',$cateId)->get()->toArray();
            $arrCateId = array_pluck($childCate,'id');

            $tagIdArr = SkillTagsModel::whereIn('cate_id',$arrCateId)->select('id')->get()->toArray();
            $tagIdArr = array_flatten($tagIdArr);
            $uidArr = UserTagsModel::whereIn('tag_id',$tagIdArr)->select('uid')->get()->toArray();
            $uidArr = array_flatten($uidArr);
            if($uidArr){
                
                $userArr = UserDetailModel::whereIn('user_detail.uid',$uidArr)
                    ->select('user_detail.uid','user_detail.introduce','user_detail.avatar','users.name')
                    ->leftJoin('users','users.id','=','user_detail.uid')
                    ->orderBy('user_detail.employee_praise_rate','DESC')->limit(6)->get()->toArray();
                if(!empty($userArr)){
                    $skillUid = array_pluck($userArr,'uid');

                    $skillUser = UserTagsModel::whereIn('uid',$skillUid)
                        ->join('skill_tags','skill_tags.id','=','tag_user.tag_id')
                        ->select('tag_user.*','skill_tags.tag_name')->get()->toArray();
                    $newSkillUser = \CommonClass::setArrayKey($skillUser,'uid',2,'tag_name');

                    foreach($userArr as $key => $value){
                        $userArr[$key]['skill'] = !empty($newSkillUser) && in_array($value['uid'],array_keys($newSkillUser)) ? $newSkillUser[$value['uid']] : [];

                    }
                }

            }
        }
        return $arr = [
            'user_Arr' => $userArr,
            'cate_id'  => $cateId,
            'cate'     => $cate
        ];
    }
}
