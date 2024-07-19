<?php
namespace SaQle\Auth\Observers;

require_once OBSERVABLE."/observable.php";
require_once ACCOUNT_N ."/notifications.php";
require_once UTILS."/communication/notifications2.php";
require_once ACCOUNT_N ."/notifications2.php";
require_once DAO2 ."/observers.php";
require_once COMMON ."/commons.php";

use SaQle\Observable\{Observer, Observable};
use SaQle\Auth\Services\AuthService;


use SaQle\Notifications as Notifications;
use SaQle\Notifications as MyNotifications;
use SaQle\Auth as Authentication;
use SaQle\Accounts as Accounts;
use SaQle\Dao as Dao;
use SaQle\Commons as Commons;


abstract class IAuthObserver implements Observer{
	 private $auth;
	 protected $redirect_to = '';
	 protected $sid = '';
	 protected $auth_source;
     public function __construct(AuthService $auth_service, $redirect_to = '', $sid = 'normal', $auth_source = 'NORMAL'){
		 $this->auth = $auth;
         $auth->attach($this);
		 $this->redirect_to = $redirect_to;
		 $this->sid = $sid;
		 $this->auth_source = $auth_source;
     }
     public function update(Observable $observable){
         if($observable === $this->auth){
             $this->do_update($observable);
         }
     }
     public abstract function do_update(AuthService $auth_service);
}

class SigninObserver extends AuthObserver{
	 public function do_update(Authentication\Auth $auth){
		 $feedback = $auth->status();
		 if($feedback['status'] == 0){
			 $_SESSION['is_user_authenticated'] = true;
			 $user = $this->sid === "normal" ? $feedback['feedback'] : $feedback['feedback']['tenant_user'];
			 (new Dao\User(login_status: 1))->filter(["user_id", $user->user_id])->update(update_fields: ["login_status"], partial: true);
			 (new Dao\TenantUser(online: 1))->filter([['user_id', 'tenant_token'], [$user->user_id, $user->tenant_token]])->update(update_fields: ["online"], partial: true);
			 $guest_email = $this->sid !== "normal" ? $feedback['feedback']['guest_user']->email : null;
			 $auth->record(user_id: $user->user_id, sid: $this->sid, guest_email: $guest_email);
			 $pf = new \Profiles\ProfileFactory();
			 $_SESSION['current_user'] = $pf->make(\Profiles\ProfileFactory::USER)->retrieveProfile(["user_id", $user->user_id], ["contacts", "skills", "interests", "tenants", "education", "experience", "tenancy"])[0];
			 $_SESSION['current_tenant'] = $pf->make(\Profiles\ProfileFactory::STUDYSPACE)->retrieveProfile(["tenant_token", $user->tenant_token], ["contacts", "locations", "apps", "departments", "groups"])[0];
			 $_SESSION['is_guest_user'] = false;
			 if($this->sid === "guest"){
				 $_SESSION['guest_user'] = $feedback['feedback']['guest_user'];
				 $_SESSION['is_guest_user'] = true;
			 }
			 $_SESSION['LAST_ACTIVITY'] = $_SERVER['REQUEST_TIME'];
			 if($this->auth_source === "NORMAL"){
				 if($this->redirect_to !== ''){
					 header("location: ".$this->redirect_to);
				 }else{
					 header("location: ".ROOT_DOMAIN);
				 }
			 }
		 }
     }
}
 
class SignoutObserver extends AuthObserver{
	 function do_update(Authentication\Auth $auth){
		 $feedback = $auth->status();
		 if($feedback['status'] == 0){
			 $signout_feedback = $feedback['feedback'];
			 $auth->record(user_id: $signout_feedback->user_id, action: "signout");
			 header("location: ".ROOT_DOMAIN."signin/");
		 }
     }
}
?>