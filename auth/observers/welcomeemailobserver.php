<?php
namespace SaQle\Auth\Observers;

use SaQle\Auth\Services\AccountsService;
use SaQle\Communication\Notifications\{Notifier, NotifierTypes};

class WelcomeEmailObserver extends IAccountObserver{
	 
	 public function do_update(AccountsService $acc_service){
		 $feedback = $acc_service->status();
		 if($feedback['status'] === 0){
		 	 $user = $feedback['feedback'];
			 $email_configurations = [
			     'rec_email'          => $user->username,
			     'rec_name'           => $user->first_name,
			     'placeholder_values' => [
			     	'userToWelcomeName'      => $user->first_name,
				     'userToWelcomeEmail'    => $user->username,
				     'userToWelcomePassword' => $user->plain_text_password
			     ],
			     'cc_address'  => [],
			     'bcc_address' => [],
			     'attachments' => []
			 ];
			 $welcome_email_setup_class = WELCOME_EMAIL_SETUP_CLASS;
			 $notifier = new Notifier(NotifierTypes::EMAIL, new $welcome_email_setup_class(...$email_configurations));
		     $result = $notifier->notify();
		 }
     }

}
?>