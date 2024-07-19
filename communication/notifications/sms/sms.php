<?php
namespace SaQle\Communication\Notifications\Sms;

class Sms implements INotification{
	 public function __construct(...$configurations){
	 }
	 public function notify(){
		 return true;
	 }
}

?>
