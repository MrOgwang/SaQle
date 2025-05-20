<?php
namespace SaQle\Communication\Notifications;

use SaQle\Communication\Notifications\Email\Email;
use SaQle\Communication\Notifications\Push\Push;
use SaQle\Communication\Notifications\Sms\Sms;

class Notifier implements INotification{
	 public function __construct(private NotifierTypes $type, private INotificationSetup $setup){
		 
	 }
	 public function notify(){
		 $notifier = match($this->type->value){
             'email' => new Email(...$this->setup->get_setup_data()),
			 'sms'   => new Sms(...$this->setup->get_setup_data()),
			 'push'  => new Push(...$this->setup->get_setup_data()),
             default => throw new \Exception('Unsupported notification type!')
         };
		 return $notifier->notify();
	 }
}
