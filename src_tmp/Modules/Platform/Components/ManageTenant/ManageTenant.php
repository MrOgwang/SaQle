<?php

namespace SaQle\Modules\Admin\Components\ManageTenant;

use SaQle\Http\Response\Message;
use SaQle\Http\Kernel\Session;
 
class ManageTenant {

	 public function get(string $slug) : Message {

	 	 $tenant_model = config('tenancy.model_class');

	 	 $tenant = $tenant_model::using(system_connection())->get()->where('slug__eq', $slug)->limit(1)->first_or_null();

	 	 if(!$tenant){
	 	 	 throw bad_request_exception("Invalid tenant identification!");
	 	 } 

	 	 $tenant_key = config('session_tenant_key');

	 	 request()->user->tenant_id = $tenant->tenant_id;

	 	 Session::set('__manage_tenant__', true);

		 return Message::redirect(route(admin_route_name('overview', '', false)) );
	 }

}