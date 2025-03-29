<?php
namespace SaQle\FeedBack;

class ExceptionFeedBack extends FeedBack{
	 //there can only be one exception feedback in memory
	 private static $instance;

	 private function __construct(){}
     private function __clone(){}
     public function __wakeup(){}

     //initialize a new exception feedback object
     public static function init(){
         if(self::$instance === null)
             self::$instance = new self();
         
         return self::$instance;
     }

     public static function extract(array $keys){
     	 $extracted = [];
     	 $feedback = self::$instance->feedback;
     	 if($feedback){
     	 	 $data = $feedback['feedback'];
     	 	 foreach($keys as $k => $v){
	     	 	 if($k === 'status'){
	     	 	 	 $extracted['status'] = $feedback['status'];
	     	 	 }elseif($k === 'message'){
	     	 	 	 $extracted['message'] = $feedback['message'];
	     	 	 }else{
	     	 	 	 $extracted[$k] = is_array($data) ? ($data[$k] ?? $v) :  ($data->$k ?? $v);
	     	 	 }
	     	 }
     	 }
     	 return $extracted ? $extracted : $keys;
     }
}
?>