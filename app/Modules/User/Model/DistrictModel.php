<?php

namespace App\Modules\User\Model;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DistrictModel extends Model
{
    protected $table = 'district';
    public $timestamps = false;
    protected $fillable = [
        'upid', 'name', 'type', 'displayorder'
    ];

    





    
    static function findAll()
    {
        return DistrictModel::with('childrenArea')->where('type', '=', 3)->get()->toArray();
    }

    
    static function findTree($pid)
    {
        $data = array();
        
        if($pid==0)
        {
            $data = self::getDistrictProvince();
        }else
        {
            
            $district_relationship = self::getDistractRelationship();
            $upid = $district_relationship[$pid];
            if($upid == 0)
            {
                
                $province_data = self::getProvinceDetail($pid);
                foreach($province_data as $v)
                {
                    if($v['upid']==$pid){
                        $data[] = $v;
                    }
                }
            }else
            {
                
                $province_detail = self::getProvicneData($upid);
                if(empty($province_detail))
                {
                    return false;
                }
                
                $province_data = self::getProvinceDetail($upid);
                foreach($province_data as $v)
                {
                    if($v['upid']==$pid){
                        $data[] = $v;
                    }
                }
            }
        }
        return $data;
    }
    static function findById($id,$fild=null)
    {
		$area_data = self::refreshAreaCache();
        $data = array();
        foreach($area_data as $k=>$v)
        {
            if(is_array($id) && in_array($v['id'],$id))
            {
                if(!is_null($fild))
                {
                    $data[] = $v[$fild];
                }else
                {
                    $data[] = $v;
                }

            }elseif($v['id']==$id)
            {
                if(!is_null($fild))
                {
                    $data = $v[$fild];
                }else
                {
                    $data = $v;
                }
            }
        }
        return $data;
    }
    
    static function getDistrictName($id)
    {
        if (is_array($id)) {
            $arrDistrictName = DistrictModel::whereIn('id', $id)->lists('name')->toArray();
            return implode('', $arrDistrictName);
        }
        $arrDistrictName = DB::table('district')->select('name')->where('id', $id)->first();
        if (!empty($arrDistrictName))
            return $arrDistrictName->name;
    }

    
    static function refreshAreaCache()
    {
        
        $district_relationship = DistrictModel::lists('upid','id')->toArray();
        Cache::put('district_relationship',$district_relationship,24*60);
        
        $province = DistrictModel::where('upid',0)->orderBy('displayorder')->get()->toArray();
        Cache::put('district_province',$province,24*60);
        
        foreach($province as $k=>$v)
        {
            
            $city_ids = DistrictModel::where('upid',$v['id'])->lists('id');
            $city_data = DistrictModel::whereIn('id',$city_ids)->orderBy('displayorder')->get()->toArray();
            
            $area_data = DistrictModel::whereIn('upid',$city_ids)->orderBy('displayorder')->get()->toArray();
            $other_data = array_merge($city_data,$area_data);
            Cache::put('district_list_'.$v['id'],$other_data,24*60);
        }
        Cache::forget('second_district');

    }

    
    static function getDistractRelationship()
    {
        if(Cache::has('district_relationship'))
        {
            $data = Cache::get('district_relationship');
        }else{
            $data = DistrictModel::lists('upid','id')->toArray();
            Cache::put('district_relationship',$data,24*60);
        }
        return $data;
    }

    
    static function getDistrictProvince()
    {
        if(Cache::has('district_province'))
        {
            $data = Cache::get('district_province');
        }else{
            $data = DistrictModel::where('upid',0)->get()->toArray();
            Cache::put('district_province',$data,24*60);
        }
        return $data;
    }

    
    static function getProvinceDetail($id)
    {
        if(Cache::has('district_list_'.$id))
        {
            $data = Cache::get('district_list_'.$id);
        }else{
            
            $city_ids = DistrictModel::where('upid',$id)->lists('id');
            $city_data = DistrictModel::whereIn('id',$city_ids)->get()->toArray();
            
            $area_data = DistrictModel::whereIn('upid',$city_ids)->get()->toArray();
            $data = array_merge($city_data,$area_data);
            Cache::put('district_list_'.$id,$data,24*60);
        }
        return $data;
    }

    
    static function getProvicneData($id)
    {
        $province_datas = Self::getDistrictProvince($id);
        $data = null;
        foreach($province_datas as $k=>$v)
        {
            if($v['id']==$id)
            {
                $data = $v;
            }
        }
        return $data;
    }

    
    static public function getAreaName($provinceId,$cityId)
    {
        $provinceName = '';
        if($provinceId){
            $province = DistrictModel::where('id',$provinceId)->select('id','name')->first();
            if($province){
                $provinceName = $province->name;
            }
        }
        $cityName = '';
        if($cityId){
            $city = DistrictModel::where('id',$cityId)->select('id','name')->first();
            if($city){
                $cityName = $city->name;
            }
        }
        if(in_array($provinceName,['北京市','上海市','天津市','重庆市'])){
            $name = $provinceName;
        }else{
            $name = $cityName;
        }
        return $name;
    }

    static public function getSecondDistrict()
    {
        if(Cache::has('second_district')){
            $data = Cache::get('second_district');
        }else{
            $province = DistrictModel::getDistrictProvince();
            $provinceIdArr = array_pluck($province,'id');
            $city = DistrictModel::whereIn('upid',$provinceIdArr)->select('id','name','upid')->get()->toArray();
            $secondDistrict = array_merge($province,$city);

            $data = [];
            if (!empty($secondDistrict)) {
                foreach ($secondDistrict as $key => $value) {
                    if ( 0 == $value['upid']) {
                        $data[$value['id']] = $value;
                        $data[$value['id']]['child'] = [];
                    } else {
                        $data[$value['upid']]['child'][] = $value;
                    }
                }
            }
            if(!empty($data)){
                foreach($data as $k => $v){
                    if(!isset($v['upid']) || $v['upid'] != 0){
                        unset($data[$k]);
                    }
                }
            }
            $data = array_values($data);
            Cache::put('second_district',$data,24*60);
        }

        return $data;

    }

}
