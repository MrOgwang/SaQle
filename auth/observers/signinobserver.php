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

		 	 $request           = Request::init();
		 	 if(!$this->redirect_to){
		 	 	 $this->redirect_to = $request->route->get_query_param('next');
		 	 	 if(!$this->redirect_to){
		 	 	 	 $this->redirect_to = $request->data->get('redirect_to', '');
		 	 	 }
		 	 }
			 /**
			 * User object
			 */
			 $user = $feedback['feedback']['user'];
			 
			 /**
			 * Tenant object
			 */
			 $tenant = array_key_exists("tenant", $feedback['feedback']) ? $feedback['feedback']['tenant'] : null;
			 
			 /**
			 * Record user log in
			 */
			 $auth_service->record_signin($user->user_id);
			 
			 /**
			 * Set user online status to true
			 */
			 $auth_service->update_online_status($user->user_id, true);
			 
			 
			 /**
			 * Set session data
			 */
			 $_SESSION['is_user_authenticated'] = true;
			 $_SESSION['user']                  = $user;
			 $_SESSION['user_has_tenant']       = $tenant ? true : false;
			 if($_SESSION['user_has_tenant']){
			 	 $_SESSION['tenant']            = $tenant;
			 }
			 $_SESSION['LAST_ACTIVITY']         = $_SERVER['REQUEST_TIME'];
			 
			 /**
			 * Redirect user to the home page or page that was requested before signin
			 */
			 if($this->redirect_to !== ''){
			     header("location: ".$this->redirect_to);
			 }else{
				 header("location: ".ROOT_DOMAIN);
			 }
		 }
     }
}

?>