<?php

/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

/**
 * Represents a http message object
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffe.omondi@saqle.com>
 * */

namespace SaQle\Http\Response;

use SaQle\Core\FeedBack\FeedBack;

class HttpMessage extends FeedBack {

	 /**
	 * Http response code description
	 * 
	 * @var string
	 * */
	 public protected(set) string $status_message = "" {
	 	 set(string $value){
	 	 	 $this->status_message = $value;
	 	 }

	 	 get => $this->status_message;
	 }

     /**
     * Create a new http message instance
     * 
     * @param int        $code
     * @param mixed      $response
     * @param string     $message
     * */
	 public function __construct(int $code, array $response = [], string $message = ""){
		 $this->set($code, $response, $message);
		 $this->status_message = $this->get_message($this->code);
	 }

	 /**
     * Set http message data
     * 
     * @return mixed
     * */
	 public function set_data(array $data){
		 $this->data = $data;
	 }

     /**
     * Construct a http message object from a feedback object
     * 
     * @param array $feedback
     * */
	 public static function from_feedback(FeedBack $fb){
		 return new self($fb->code, $fb->data, $fb->message);
	 }
}
?>