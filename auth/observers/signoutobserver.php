<?php
namespace SaQle\Auth\Observers;

use SaQle\Core\Services\Observer\AppServiceObserver;
use SaQle\Core\Services\IService;
use SaQle\Core\FeedBack\FeedBack;
 
class SignoutObserver extends AppServiceObserver{
	 public function handle(IService $service){
	 	 $status = $service->status();
		 if($status->code == FeedBack::OK && $status->data){
		 	 $user = $status->data['result'];
		 	 $service->record_signout(user_id: $user->user_id);
		 	 header("Location: ".ROOT_DOMAIN);
		 }
     }
}
