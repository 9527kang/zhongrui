<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    
    protected $except = [
        

        'order/pay/alipay/notify',
        'order/pay/wechat/notify',

        
        'api/*',

        
        'user/goodsCashValid',

        '/fastPub',
    ];

    public function handle($request, \Closure $next)
    {
        
        try{
             return parent::handle($request, $next); 
        }catch (\Exception $e){
            return redirect()->back();
        }

    }
}
