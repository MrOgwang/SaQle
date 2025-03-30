<?php
namespace SaQle\Auth\Observers;

use SaQle\Observable\{Observer, Observable};
use SaQle\Auth\Services\AuthService;
use SaQle\Http\Request\Request;
use SaQle\FeedBack\FeedBack;
 
class SignoutObserver extends IAuthObserver{
	 function do_update(AuthService $auth_service){
	 	 $feedback = $auth_service->status();
	 	 print_r($feedback);
		 if($feedback['status'] == FeedBack::SUCCESS && $feedback['feedback'] && $feedback['action'] === 'signout'){
		 	 $user = $feedback['feedback'];
		 	 $auth_service->record_signout(user_id: $user->user_id);
		 	 header("Location: ".ROOT_DOMAIN);
		 }
     }
}
?>