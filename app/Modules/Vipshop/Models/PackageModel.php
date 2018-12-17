<?php

namespace App\Modules\Vipshop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Modules\Vipshop\Models\PackagePrivilegesModel;
use App\Modules\Vipshop\Models\PrivilegesModel;

class PackageModel extends Model
{
    use SoftDeletes;
    protected $table = 'package';

    protected $primaryKey = 'id';

    protected $fillable = [

        'title', 'logo', 'status', 'price_rules', 'list', 'created_at', 'updated_at','deleted_at','type','type_status'

    ];
    protected $datas = ['deleted_at'];

    
    static function packageList(){
        $packageInfo = PackageModel::select('*')->orderBy('list','asc')->orderBy('created_at','desc')->paginate(10);
        if($packageInfo->total()){
            foreach($packageInfo->items() as $k=>$v){
                if(is_array(json_decode($v->price_rules,true))){
                    $v->price = collect(array_pluck(json_decode($v->price_rules,true),'cash'))->sort()->values()->first();
                }
            }
        }
        return $packageInfo;
    }

    
    static function updateStatus($id){
        $packageInfo = PackageModel::find(intval($id));
        if(empty($packageInfo)){
            return 2;                
        }else{
            if($packageInfo->status == 0){
                $res = $packageInfo->update(['status' => 1,'updated_at' => date('Y-m-d H:i:s',time())]);
            }else{
                $num = PackageModel::where('status',0)->count();
                if($num >= 5){
                    return 3;              
                }
                $res = $packageInfo->update(['status' => 0,'updated_at' => date('Y-m-d H:i:s',time())]);
            }
            return $res?1:0;                     
        }
    }

    
    static function deletePackage($id){
        $packageInfo = PackageModel::find(intval($id));
        if(empty($packageInfo)){
            return 2;           
        }
        $res = $packageInfo->delete();
        return $res?1:0;        
    }

    
    static function addPackage(array $data){
        $price_rules = json_encode($data['price_rules']);
        $packageInfo = [
            'title' => $data['title'],
            'logo' => $data['logo'],
            'status' => $data['status']?0:1,
            'price_rules' => $price_rules,
            'list' => $data['list'],
            'created_at' => date('Y-m-d H:i:s',time()),
            'updated_at' => date('Y-m-d H:i:s',time())
        ];
        $ruleMore = [
            'TASK_WORK' => isset($data['TASK_WORK']) && !empty($data['TASK_WORK']) ? $data['TASK_WORK'] : '',
            'MOST_TASK_BOUNTY' => isset($data['MOST_TASK_BOUNTY']) && !empty($data['MOST_TASK_BOUNTY']) ? $data['MOST_TASK_BOUNTY'] : '',
            'SKILL_TAGS_NUM' => isset($data['SKILL_TAGS_NUM']) && !empty($data['SKILL_TAGS_NUM']) ? $data['SKILL_TAGS_NUM'] : '',
            'SERVICE_OFF' => isset($data['SERVICE_OFF']) && !empty($data['SERVICE_OFF']) ? $data['SERVICE_OFF'] : '',
        ];
        $package['privileges'] = $data['privileges'];
        $package['packageInfo'] = $packageInfo;
        $package['more_rule'] = $ruleMore;
        $res = DB::transaction(function()use($package){
            $packageId = PackageModel::insertGetId($package['packageInfo']);

            $code = [];
            if(!empty($package['more_rule'])){
                $privilegesArr = PrivilegesModel::whereIn('code',array_keys($package['more_rule']))->select('id','code')->get()->toArray();
                $privilegesArr = \CommonClass::setArrayKey($privilegesArr,'code');
                foreach($package['more_rule'] as $k => $v){
                    if(!empty($v) && in_array($k,array_keys($privilegesArr))){
                        $code[$privilegesArr[$k]['id']] = $v;
                    }
                }
            }
            $privilegesInfo = [];
            foreach($package['privileges'] as $k => $v){
                $privilegesInfo[$k]['package_id'] = $packageId;
                $privilegesInfo[$k]['privileges_id'] = $v;
                $privilegesInfo[$k]['rule'] = '';
                if(in_array($v,array_keys($code))){
                    $privilegesInfo[$k]['rule'] = $code[$v];
                }
            }
            PackagePrivilegesModel::insert($privilegesInfo);
        });
        return is_null($res)?true:false;    
    }

    
    static function privileges(){
        $privileges = [];
        $privilegesInfo = PrivilegesModel::where('status',0)->orderBy('list','asc')->select('id','title','code')->get()->toArray();
        if(!empty($privilegesInfo)){
            $privileges = $privilegesInfo;
        }
        return $privileges;
    }

    
    static function packageInfo($id){
        $packageInfo = PackageModel::where('id',intval($id))->first();
        if(empty($packageInfo)){
            return false;
        }
        $packageInfo['price_rules'] = json_decode($packageInfo['price_rules'],true);
        $privilegesChk = [];
        $moreRule = [];
        $privileges = PackagePrivilegesModel::join('privileges','privileges.id','=','package_privileges.privileges_id')->where('package_privileges.package_id',intval($id))->select('package_privileges.privileges_id','package_privileges.rule','privileges.code')->get()->toArray();
        if(!empty($privileges)){
            $privilegesChk = array_pluck($privileges,'privileges_id');
            $moreRule = \CommonClass::setArrayKey($privileges,'code');
        }
        $packageInfo['privileges'] = $privilegesChk;
        $packageInfo['more_rule'] = $moreRule;
        return $packageInfo;

    }

    
    static function updatePackage($id,array $data){
        $price_rules = json_encode($data['price_rules']);
        $packageInfo = [
            'title' => $data['title'],
            
            'status' => $data['status']?0:1,
            'price_rules' => $price_rules,
            'list' => $data['list'],
            'updated_at' => date('Y-m-d H:i:s',time())
        ];
        $packageInfo['type_status'] = 1;
        if(isset($data['logo']) && $data['logo']){
            $packageInfo['logo'] = $data['logo'];
        }
        $package['privileges'] = $data['privileges'];
        $package['packageInfo'] = $packageInfo;
        $package['id'] = intval($id);
        $ruleMore = [
            'TASK_WORK' => isset($data['TASK_WORK']) && !empty($data['TASK_WORK']) ? $data['TASK_WORK'] : '',
            'MOST_TASK_BOUNTY' => isset($data['MOST_TASK_BOUNTY']) && !empty($data['MOST_TASK_BOUNTY']) ? $data['MOST_TASK_BOUNTY'] : '',
            'SKILL_TAGS_NUM' => isset($data['SKILL_TAGS_NUM']) && !empty($data['SKILL_TAGS_NUM']) ? $data['SKILL_TAGS_NUM'] : '',
            'SERVICE_OFF' => isset($data['SERVICE_OFF']) && !empty($data['SERVICE_OFF']) ? $data['SERVICE_OFF'] : '',
        ];
        $package['more_rule'] = $ruleMore;
        $res = DB::transaction(function()use($package){
            PackageModel::where('id',$package['id'])->update($package['packageInfo']);
            $code = [];
            if(!empty($package['more_rule'])){
                $privilegesArr = PrivilegesModel::whereIn('code',array_keys($package['more_rule']))->select('id','code')->get()->toArray();
                $privilegesArr = \CommonClass::setArrayKey($privilegesArr,'code');
                foreach($package['more_rule'] as $k => $v){
                    if(!empty($v) && in_array($k,array_keys($privilegesArr))){
                        $code[$privilegesArr[$k]['id']] = $v;
                    }
                }
            }

            PackagePrivilegesModel::where('package_id',$package['id'])->delete();
            $privilegesInfo = [];
            foreach($package['privileges'] as $k=>$v){
                $privilegesInfo[$k]['package_id'] = $package['id'];
                $privilegesInfo[$k]['privileges_id'] = $v;
                $privilegesInfo[$k]['rule'] = '';
                if(in_array($v,array_keys($code))){
                    $privilegesInfo[$k]['rule'] = $code[$v];
                }
            }
            PackagePrivilegesModel::insert($privilegesInfo);
        });
        return is_null($res)?true:false;       
    }
}
