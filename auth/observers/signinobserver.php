<?php
namespace SaQle\Auth\Observers;

use SaQle\Core\Services\Observer\AppServiceObserver;
use SaQle\Core\Services\IService;
use SaQle\Core\FeedBack\FeedBack;

class SigninObserver extends AppServiceObserver{
	 public function handle(IService $service){
	 	 //print_r($service);
	 	 $status = $service->status();
		 if($status->code == FeedBack::OK && $status->data){

		 	 //print_r($status->data['result']);

		 	 $user = $status->data['result'];

		 	 $request = resolve('request');
		 	 //set request user
		 	 $request->context->set('user', $user, true);

	         //record user login
			 $service->record_signin($user->user_id);
			 
			 //Set user online status to true
			 $service->update_online_status($user->user_id, true);

             //if this is not an api request
			 if(!$request->is_api_request()){

                 //set redirect location
			 	 $this->redirect_to = $request->data->get('redirect_to', $request->route->queries->get('next', ''));

			 	 //regenerate session id and set session data
			 	 $tenant = null;
			 	 session_regenerate_id();
			 	 $request->context->set('is_user_authenticated', true, true);
			 	 $request->context->set('user_has_tenant', $tenant ? true : false, true);
			 	 $request->context->set('tenant', $tenant, true);
			 	 $request->context->set('LAST_ACTIVITY', $_SERVER['REQUEST_TIME'], true);

			     //redirect user to relevant location
			     if($this->redirect_to !== ''){
				     header("location: ".$this->redirect_to);
				 }else{
					 header("location: ".ROOT_DOMAIN);
				 }
			 }
		 }
	 }
}

?>