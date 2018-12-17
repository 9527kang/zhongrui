<?php

namespace App\Modules\Task\Model;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class ServiceModel extends Model
{

    protected $table = 'service';

    
    static public function serviceMoney($product_ids,$off=100)
    {

        $money = 0;
        foreach($product_ids as $k=>$v) {
            $data = self::where('id','=',$v)->first();
            if(!empty($data)){
                $data = $data->toArray();
                $price = floatval(number_format($data['price']*$off/100,2));
                $money = $money + $price;
            }

        }
        return $money;
    }

}
