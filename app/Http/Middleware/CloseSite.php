<?php

namespace App\Http\Middleware;

use App\Modules\Manage\Model\ConfigModel;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;

class CloseSite
{
    
    protected $auth;

    

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    
    public function handle($request, Closure $next)
    {
        $fileLockPath = base_path('kppw.install.lck');
        if (file_exists($fileLockPath)){
            $siteConfig = ConfigModel::getConfigByType('site');
            if ($siteConfig['site_close'] == 2){
                return response()->view('errors.400', ['site_close_desc' => $siteConfig['site_close_desc']], 400);
            }
        }
        return $next($request);
    }
}
