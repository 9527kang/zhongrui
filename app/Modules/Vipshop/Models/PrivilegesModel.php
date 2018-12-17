<?php

namespace App\Modules\Vipshop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Vipshop\Models\PackagePrivilegesModel;
use App\Modules\Vipshop\Models\PackageModel;
use DB;

class PrivilegesModel extends Model
{
    use SoftDeletes;
    protected $table = 'privileges';

    protected $primaryKey = 'id';

    protected $fillable = [

        'title', 'desc', 'code', 'list', 'type', 'status','rule', 'is_recommend', 'ico','created_at','updated_at','deleted_at'

    ];
    protected $datas = ['deleted_at'];

    
    static function privilegesList($data){
        $privileges = PrivilegesModel::select('*');
        if(isset($data['title'])){
            $privileges = $privileges->where('title','like','%'.$data['title'].'%');
        }
        if(isset($data['status']) && $data['status']){
            switch($data['status']){
                case '1':      
                    $status = 0;
                    $privileges = $privileges->where('status',$status);
                    break;
                case '2':      
                    $status = 1;
                    $privileges = $privileges->where('status',$status);
                    break;

            }
        }
        if(isset($data['is_recommend']) && $data['is_recommend']){
            switch($data['is_recommend']){
                case '1':      
                    $is_recommend = 1;
                    $privileges = $privileges->where('is_recommend',$is_recommend);
                    break;
                case '2':      
                    $is_recommend = 0;
                    $privileges = $privileges->where('is_recommend',$is_recommend);
                    break;

            }
        }
        $privileges = $privileges->orderBy('list','asc')->orderBy('created_at','desc')->paginate(10);
        if($privileges->total()){
            foreach($privileges->items() as $k=>$v){
                $v->desc = substr_replace($v->desc,'...',15);
            }
        }
        return $privileges;

    }

    
    static function deletePrivileges($id){
        $id = intval($id);
        $privilegesInfo = PrivilegesModel::find($id);
        if(empty($privilegesInfo)){
            return 2;
        }
        $res = DB::transaction(function() use($id){
            $packagePrivileges = PackagePrivilegesModel::where('privileges_id',$id)->delete();
            $privileges = PrivilegesModel::where('id',$id)->delete();
        });
        return is_null($res)?1:0;          
    }

    
    static function updateStatus($id){
        $id = intval($id);
        $privilegesInfo = PrivilegesModel::find($id);
        if(empty($privilegesInfo)){
            return 2;
        }
        if($privilegesInfo->status == 0){
            
            $res = DB::transaction(function() use($id){
                $privileges = PrivilegesModel::where('id',$id)->update(['status' => 1,'updated_at' => date('Y-m-d H:i:s',time())]);
                $packagePrivileges = PackagePrivilegesModel::where('privileges_id',$id)->delete();
            });
            return is_null($res)?1:0;                      
        }else{
            $res = $privilegesInfo->update(['status' => 0,'updated_at' => date('Y-m-d H:i:s',time())]);
            return $res?1:0;                             
        }
    }


    
    static function updateRecommend($id){
        $privilegesInfo = PrivilegesModel::find(intval($id));
        if(empty($privilegesInfo)){
            return 2;
        }
        if($privilegesInfo->is_recommend == 0){
            $num = PrivilegesModel::where('is_recommend',1)->count();
            if($num >= 6){
                return 3;              
            }
            $res = $privilegesInfo->update(['is_recommend' => 1,'updated_at' => date('Y-m-d H:i:s',time())]);
        }else{
            $res = $privilegesInfo->update(['is_recommend' => 0,'updated_at' => date('Y-m-d H:i:s',time())]);
        }
        return $res?1:0;                             
    }


    
    static function addPrivileges(array $data){
        $privilegeInfo = [
            'title' => $data['title'],
            'desc' => $data['desc'],
            'list' => $data['list'],
            'ico' => $data['ico'],
            'status' => $data['status']?0:1,
            'is_recommend' => $data['is_recommend']?1:0,
            'created_at' => date('Y-m-d H:i:s',time()),
            'updated_at' => date('Y-m-d H:i:s',time())
        ];
        $res = PrivilegesModel::create($privilegeInfo);
        return $res?true:false;                     
    }

    
    static function privilegesDetail($id){
        $privilegesInfo = PrivilegesModel::where('id',intval($id))->first();
        if(empty($privilegesInfo)){
            return false;
        }
        return $privilegesInfo;
    }

    
    static function updatePrivileges($id,array $data){
        $privilegeInfo = [
            'title' => $data['title'],
            'desc' => $data['desc'],
            'list' => $data['list'],
            'status' => $data['status']?0:1,
            'is_recommend' => $data['is_recommend']?1:0,
            'created_at' => date('Y-m-d H:i:s',time()),
            'updated_at' => date('Y-m-d H:i:s',time())
        ];
        if(isset($data['ico']) && $data['ico']){
            $privilegeInfo['ico'] = $data['ico'];
        }
        $res = PrivilegesModel::where('id',intval($id))->update($privilegeInfo);
        return $res?true:false;                     
    }

    
    static public function getSystemPrivileges()
    {
        $list = self::where('type',1)->select('id','code','title')->get()->toArray();
        $list = \CommonClass::setArrayKey($list,'id');
        return $list;
    }

    
    static public function getSystemPackage()
    {
        $package = PackageModel::where('type',1)->first();
        if(!$package){
            return false;
        }
        $rule = self::getPackageConfigById($package->id);
        return $rule;

    }

    static public function getPackageConfigById($id)
    {
        $list = self::getSystemPrivileges();
        $rule = PackagePrivilegesModel::where('package_id',$id)->whereIn('privileges_id',array_keys($list))->leftJoin('privileges','privileges.id','=','package_privileges.privileges_id')->select('privileges_id','package_privileges.rule','privileges.code')->get()->toArray();
        $rule = \CommonClass::setArrayKey($rule,'code');
        return $rule;
    }
}