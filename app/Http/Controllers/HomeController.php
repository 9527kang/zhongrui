<?php


namespace App\Http\Controllers;


use App\Modules\Advertisement\Model\RePositionModel;
use App\Modules\Finance\Model\CashoutModel;
use App\Modules\Manage\Model\FastTaskModel;
use App\Modules\Manage\Model\LinkModel;
use App\Modules\Task\Model\TaskCateModel;
use App\Modules\Task\Model\WorkModel;
use App\Modules\User\Model\SkillTagsModel;
use App\Modules\User\Model\TaskModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use App\Modules\User\Model\UserTagsModel;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Modules\Advertisement\Model\AdTargetModel;
use App\Modules\Manage\Model\ConfigModel;
use Cache;
use Illuminate\Support\Facades\Session;
use Teepluss\Theme\Theme;


class HomeController extends IndexController
{
    public function __construct()
    {

        parent::__construct();
        $this->initTheme('common');
    }

    
    public function index()
    {
        
        $banner = \CommonClass::getHomepageBanner();
        $this->theme->set('banner', $banner);

        
        $notice = \CommonClass::getHomepageNotice();
        $this->theme->set('notice',$notice);

        
        $taskWin = WorkModel::where('work.status',1)->join('users','users.id','=','work.uid')
            ->leftJoin('task','task.id','=','work.task_id')
            ->select('work.*','users.name','task.show_cash','task.title')
            ->orderBy('work.bid_at','Desc')->limit(5)->get()->toArray();
        $this->theme->set('task_win',$taskWin);

        
        $withdraw = CashoutModel::where('cashout.status',1)->join('users','users.id','=','cashout.uid')
            ->select('cashout.*','users.name')
            ->orderBy('cashout.updated_at','DESC')->limit(5)->get()->toArray();
        $this->theme->set('withdraw',$withdraw);

        
        $user = \CommonClass::getPhone();
        $this->theme->set('complaints_user',$user);

        
        $task = TaskModel::getNewTask(15);
        
        $active = TaskModel::getNewWorkBid(10);

        
        $recommendShop = RePositionModel::getHomeRecommendShop();

        $count = count($recommendShop['shop_before']);
        $recommendShopArr = array();
        
        for($a=0;$a<$count;$a=$a+2) {
            if(isset($recommendShop['shop_before'][$a+1])) {
                $reArr = array($recommendShop['shop_before'][$a],$recommendShop['shop_before'][$a+1]);
            } else {
                $reArr = array($recommendShop['shop_before'][$a]);
            }
            $recommendShopArr[] = $reArr;
        }

        
        $recommendWork = RePositionModel::getHomeRecommendWork();

        
        $recommendServer = RePositionModel::getHomeRecommendService();

        
        $recommendSuccess = RePositionModel::getHomeRecommendSuccess();

        
        $article = RePositionModel::getHomeRecommendArticle();

        
        $friendUrl = LinkModel::where('status',1)->orderBy('sort','ASC')->orderBy('addTime','DESC')->get()->toArray();
        
        $ad = AdTargetModel::getAdInfo('HOME_BOTTOM');
        $data = array(
            'task' => $task,
            'active' => $active,
            'recommend_shop' => $recommendShop['recommend_shop'],
            'shop_before' => $recommendShop['shop_before'],
            'shop' => $recommendShopArr,
            'recommend_work' => $recommendWork['recommend_work'],
            'work' => $recommendWork['work'],
            'recommend_server' => $recommendServer['recommend_server'],
            'server' => $recommendServer['server'],
            'success' => $recommendSuccess['success'],
            'recommend_success' =>$recommendSuccess['recommend_success'],
            'articleArr' => $article['articleArr'],
            'article' => $article['article'],
            'recommend_article' => $article['recommend_article'],
            'friendUrl' => $friendUrl,
            'ad' => $ad
        );

        if ($this->themeName == 'black'){
            $blackHome = RePositionModel::getBlackHome();

            $data['service'] = $blackHome['service'];
            $data['goods'] = json_encode($blackHome['goods_info']);

            $data['danmu'] = json_encode($data['task']);
        }

        
        if($this->themeName == 'zbj'){
            
            $adZbj = AdTargetModel::getAdInfo('HOME_NEWTASK');
            $data['adZbj'] = $adZbj;
            $zbj = RePositionModel::getZbjHome();
            $data['user_Arr'] = $zbj['user_Arr'];
            $data['cate_id'] = $zbj['cate_id'];
            $data['cate'] = $zbj['cate'];
        }

        if($this->themeName == 'default'){
            
            $adSide = AdTargetModel::getAdInfo('HOME_SUCCESS_SIDE');
            $data['adSuccessSide'] = $adSide;
        }

        
        $seoConfig = ConfigModel::getConfigByType('seo');

        if(!empty($seoConfig['seo_index']) && is_array($seoConfig['seo_index'])){
            $this->theme->setTitle($seoConfig['seo_index']['title']);
            $this->theme->set('keywords',$seoConfig['seo_index']['keywords']);
            $this->theme->set('description',$seoConfig['seo_index']['description']);
        }else{
            $this->theme->setTitle('威客|系统—客客出品,专业威客建站系统开源平台');
            $this->theme->set('keywords','威客,众包,众包建站,威客建站,建站系统,在线交易平台');
            $this->theme->set('description','客客专业开源建站系统，国内外知名站长使用最多的众包威客系统，建在线交易平台。');
        }
        $this->theme->set('now_menu','/');
        return $this->theme->scope('bre.homepage',$data)->render();

    }



    public function changeCate(Request $request,$id)
    {
        $this->initTheme('ajaxpage');
        $cateId = $id;
        $userArr = [];
        
        $childCate = TaskCateModel::where('pid',$cateId)->get()->toArray();
        $arrCateId = array_reduce($childCate,function(&$arrCateId,$v){
            $arrCateId[] = $v['id'];
            return $arrCateId;
        });
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
                $skillUid = array_reduce($userArr,function(&$skillUid,$v){
                    $skillUid[] = $v['uid'];
                    return $skillUid;
                });
                $skillUser = UserTagsModel::whereIn('uid',$skillUid)
                    ->join('skill_tags','skill_tags.id','=','tag_user.tag_id')
                    ->select('tag_user.*','skill_tags.tag_name')->get()->toArray();
                $newSkillUser = [];
                if(!empty($skillUser)){
                    $newSkillUser = array_reduce($skillUser,function(&$newSkillUser,$v){
                        $newSkillUser[$v['uid']][] = $v['tag_name'];
                        return $newSkillUser;
                    });
                }
                foreach($userArr as $key => $value){
                    if(!empty($newSkillUser)){
                        foreach($newSkillUser as $k => $v){
                            if($value['uid'] == $k){
                                $userArr[$key]['skill'] = $v;
                            }
                        }
                    }

                }
            }

        }
        $data['user_Arr'] = $userArr;
        return $this->theme->scope('bre.ajaxchangecate',$data)->render();
    }

    public function sendTaskCode(Request $request)
    {
        $arr = $request->all();

        $code = rand(1000, 9999);

        $scheme = ConfigModel::phpSmsConfig('phpsms_scheme');
        $templateId = ConfigModel::phpSmsConfig('sendTaskCode');

        $templates = [
            $scheme => $templateId,
        ];

        $tempData = [
            'code' => $code,
        ];

        $status = \SmsClass::sendSms($arr['mobile'], $templates, $tempData);

        if ($status['success'] == true) {
            $data = [
                'code' => $code,
                'mobile' => $arr['mobile']
            ];
            Session::put('task_mobile_info', $data);
            return ['code' => 1000, 'msg' => '短信发送成功','data' => $code];
        } else {
            return ['code' => 1001, 'msg' => '短信发送失败'];
        }

    }

    
    public function fastPub(Request $request)
    {
        $data = $request->all();

        $taskMobileInfo = session('task_mobile_info');

        if ($data['code'] == $taskMobileInfo['code'] && $data['mobile'] == $taskMobileInfo['mobile']) {
            Session::forget('auth_mobile_info');
            
            $user = UserModel::where('mobile',$data['mobile'])->first();
            if($user){
                $uid = $user['id'];
            }else{
                $username = \CommonClass::random(2).$data['mobile'];
                $userInfo = [
                    'username' => $username,
                    'mobile' => $data['mobile'],
                    'password' => $data['mobile']
                ];
                $uid = UserModel::mobileInitUser($userInfo);
                
                $scheme = ConfigModel::phpSmsConfig('phpsms_scheme');
                $templateId = ConfigModel::phpSmsConfig('sendUserPassword');
                $templates = [
                    $scheme => $templateId,
                ];
                $tempData = [
                    'mobile' => $data['mobile'],
                    'password' => $data['mobile'],
                    'website' => $this->theme->get('site_config')['site_url']
                ];

                \SmsClass::sendSms($data['mobile'], $templates, $tempData);

            }
            $data['uid'] = $uid;
            $res = FastTaskModel::create($data);
            if($res){
                return $arr = [
                    'code' => 1,
                    'msg'  => '发布成功'
                ];
            }else{
                return $arr = [
                    'code' => 0,
                    'msg'  => '发布失败'
                ];
            }
        }else{
            return $arr = [
                'code' => 0,
                'msg'  => '验证码错误'
            ];
        }

    }





}