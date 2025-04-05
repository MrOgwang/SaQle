<?php
namespace SaQle\Core\Observable;

use SaQle\Core\FeedBack\FeedBack;

trait ConcreteObservable {
	 protected $observers;
	 protected $feedback;
	 public function __construct(){
		 $this->observers = [];
		 $this->feedback = new FeedBack();
	 }
	 public function attach(Observer $observer){
         $this->observers[] = $observer;
     }
     public function attach_all(array $observers){
     	 foreach($observers as $obs){
     	 	 $observer = is_string($obs) ? new $obs() : $obs;
     	 	 $this->observers[] = $observer;
     	 }
     }
     public function detach(Observer $observer){
         $this->observers = array_filter($this->observers,  function($a) use ($observer){return (!($a === $observer));});
     }
     public function detach_all(){
         foreach($this->observers as $obs){
         	 $this->detach($obs);
         }
	 }
     public function notify(){
         foreach($this->observers as $obs){
             $obs->update($this);
         }
	 }
	 public function status(){
		 return $this->feedback;
	 }
	 public function quick_notify(array $observers, int $code, mixed $data = null, string $message = '', string $action = ''){
	 	 $this->attach_all($observers);
	 	 $this->feedback->set($code, $data, $message, $action);
	 	 $this->notify();
	 	 $this->detach_all();
	 }
}
?>