<?php
namespace SaQle\Observable;

use SaQle\FeedBack\FeedBack;

trait ConcreteObservable{
	 protected $observers;
	 protected $feedback;
	 public function __construct(){
		 $this->observers = [];
		 $this->feedback = new FeedBack();
	 }
	 public function attach(Observer $observer){
         $this->observers[] = $observer;
     }
     public function detach(Observer $observer){
         $this->observers = array_filter($this->observers,  function($a) use ($observer){return (!($a === $observer));});
     }
     public function notify(){
         foreach($this->observers as $obs){
             $obs->update($this);
         }
	 }
	 public function status(){
		 return $this->feedback->get_feedback();
	 }
	 public function get_feedback(){
	 	return $this->feedback;
	 }
}
?>