<?php
namespace SaQle\Auth\Observers;

use SaQle\Observable\{Observer, Observable};
use SaQle\Auth\Services\AuthService;
use SaQle\Http\Request\Request;
use SaQle\Core\FeedBack\FeedBack;
 
class SignoutObserver extends IAuthObserver{
	 function do_update(AuthService $auth_service){
	 	 $fb = $auth_service->status();
		 if($fb->code == FeedBack::OK && $fb->data && $fb->action === 'signout'){
		 	 $auth_service->record_signout(user_id: $fb->data->user_id);
		 	 header("Location: ".ROOT_DOMAIN);
		 }
     }
}
?>