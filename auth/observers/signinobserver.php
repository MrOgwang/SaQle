<?php
namespace SaQle\Auth\Observers;

use SaQle\Observable\{Observer, Observable};
use SaQle\Auth\Services\AuthService;


use SaQle\Notifications as Notifications;
use SaQle\Notifications as MyNotifications;
use SaQle\Auth as Authentication;
use SaQle\Accounts as Accounts;
use SaQle\Dao as Dao;
use SaQle\Commons as Commons;
use SaQle\Http\Request\Request;

class SigninObserver extends IAuthObserver{
	
	 public function do_update(AuthService $auth_service){
		 $feedback = $auth_service->status();
		 if($feedback['status'] == 0){

		 	 $request = Request::init();

	         //record user login
			 $auth_service->record_signin($feedback['feedback']['user']->user_id);
			 
			 //Set user online status to true
			 $auth_service->update_online_status($feedback['feedback']['user']->user_id, true);

             //if this is not an api request
			 if(!$request->route->is_api_request()){

                 //set redirect location
			 	 $this->redirect_to = $this->request->data->get('redirect_to', $this->request->route->get_query_param('next', ''));

			 	 //set session data
			 	 $tenant = array_key_exists("tenant", $feedback['feedback']) ? $feedback['feedback']['tenant'] : null;
			 	 $_SESSION['is_user_authenticated'] = true;
			     $_SESSION['user']                  = $feedback['feedback']['user'];
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