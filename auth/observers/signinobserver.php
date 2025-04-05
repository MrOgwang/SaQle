<?php
namespace SaQle\Auth\Observers;

use SaQle\Observable\{Observer, Observable};
use SaQle\Auth\Services\AuthService;
use SaQle\Http\Request\Request;
use SaQle\Core\FeedBack\FeedBack;

class SigninObserver extends IAuthObserver{
	
	 public function do_update(AuthService $auth_service){
		 $fb = $auth_service->status();
		 if($fb->code == FeedBack::OK && $fb->data && $fb->action === 'signin'){

		 	 $user = $fb->data;

		 	 $request = Request::init();
		 	 //set request user
		 	 $request->user = $user;

	         //record user login
			 $auth_service->record_signin($user->user_id);
			 
			 //Set user online status to true
			 $auth_service->update_online_status($user->user_id, true);

             //if this is not an api request
			 if(!$request->is_api_request()){

                 //set redirect location
			 	 $this->redirect_to = $request->data->get('redirect_to', $request->route->queries->get('next', ''));

			 	 //regenerate session id and set session data
			 	 $tenant = null;
			 	 session_regenerate_id();
			 	 $_SESSION['is_user_authenticated'] = true;
			     $_SESSION['user']                  = $user;
			     $_SESSION['user_has_tenant']       = $tenant ? true : false;
			     if($_SESSION['user_has_tenant']){
			 	     $_SESSION['tenant']            = $tenant;
			     }
			     $_SESSION['LAST_ACTIVITY']         = $_SERVER['REQUEST_TIME'];

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