<?php

namespace App\Modules\Substation\Providers;

use Caffeinated\Modules\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

class RouteServiceProvider extends ServiceProvider
{
	
	protected $namespace = 'App\Modules\Substation\Http\Controllers';

	
	public function boot(Router $router)
	{
		parent::boot($router);

		
	}

	
	public function map(Router $router)
	{
		$router->group([
			'namespace'  => $this->namespace,
		], function($router) {
			require (config('modules.path').'/Substation/Http/routes.php');
		});
	}
}
