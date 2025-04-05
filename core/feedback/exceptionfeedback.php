<?php
namespace SaQle\Core\FeedBack;

class ExceptionFeedBack extends FeedBack {
	 private static $instance;
	 private bool $active = false;

	 private function __construct(){}
     private function __clone(){}
     public function __wakeup(){}

     public static function init(){
         if(self::$instance === null)
             self::$instance = new self();
         
         return self::$instance;
     }

     public function set(int $code, mixed $data = [], ?string $message = null, string $action = ''){
     	 $this->active  = true;
     	 parent::set($code, $data, $message, $action);
     }

     public function acquire_context(){
     	 if(!$this->active)
     	 	 return ['f_http_response_code' => FeedBack::OK, 'f_http_response_message' => ''];

     	 $context = [
     	 	 'f_http_response_code'    => $this->code,
     	 	 'f_http_response_message' => $this->message
     	 ];

     	 foreach($this->data as $dk => $dv){
     	 	 $context[$dk] = $dv;
     	 }

     	 $this->active = false;

     	 return $context;
     }
}
?>