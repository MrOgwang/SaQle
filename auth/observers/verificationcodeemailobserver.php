<?php
namespace SaQle\Auth\Observers;

use SaQle\Auth\Services\AccountsService;
use SaQle\Auth\Notifications\VerificationCodeEmailSetup;
use SaQle\Communication\Notifications\{Notifier, NotifierTypes};

class VerificationCodeEmailObserver extends IAccountObserver{
	 public function do_update(AccountsService $acc_service){
		 $feedback = $acc_service->status();
		 if($feedback['status'] === 0){
		 	 $code = $feedback['feedback'];
			 $email_configurations = [
			     'rec_email'          => $code->email,
			     'rec_name'           => '',
			     'placeholder_values' => [
				     'verificationCode' => $code->code,
			     ],
			     'cc_address'  => [],
			     'bcc_address' => [],
			     'attachments' => []
			 ];
			 $verification_email_setup_class = VERIFICATION_EMAIL_SETUP_CLASS;
			 $notifier = new Notifier(NotifierTypes::EMAIL, new $verification_email_setup_class(...$email_configurations));
		     $result = $notifier->notify();
		 }
     }
}
?>