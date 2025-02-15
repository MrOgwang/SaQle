<?php
namespace SaQle\FeedBack;

class FeedBack{
	 const INVALID_INPUT = 400;
	 const DB_ERROR = 500;
	 const GENERAL_ERROR = 500;
	 const SUCCESS = 200;
	 private $feedback;
	 public function __construct(){
		 $this->feedback = ["status" => 0, "feedback" => null, "message" => null];
	 }
	 public function set($status = 0, $feedback = null, $message = null){
		 $this->feedback['status'] = $status;
		 $this->feedback['feedback'] = $feedback;
		 $this->feedback['message'] = $message;
	 }
	 public function get_feedback(){
		 return $this->feedback;
	 }
	 public function get($status = 0, $feedback = null, $message = null){
		 $this->feedback['status'] = $status;
		 $this->feedback['feedback'] = $feedback;
		 $this->feedback['message'] = $message;
		 return $this->feedback;
	 }
}
?>