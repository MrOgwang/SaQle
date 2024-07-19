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

use SaQle\FeedBack\FeedBack;

class HttpMessage{
	/**
	 * Http method (POST, GET, PUT etc)
	 * 
	 * @var string
	 * */
	private string $method = "";

	/**
	 * Http response code
	 * 
	 * @var int
	 * */
	private int    $code;

	/**
	 * Http response code description
	 * 
	 * @var string
	 * */
	private string $status_message = "";

	/**
	 * Custom message from application
	 * 
	 * @var string
	 * */
	private string $message        = "";

	/**
	 * Http message data
	 * 
	 * @var mixed
	 * */
	private        $response;

    /**
     * Create a new http message instance
     * 
     * @param StatusCode $code
     * @param mixed      $response
     * @param string     $message
     * */
	public function __construct(StatusCode $code, $response = null, string $message = ""){
		$this->code     = $code->value;
		$this->response = $response;
		$this->set_status_message();
		$this->message  = $message ? $message : $this->status_message;
	}

    /**
     * Get http message method
     * 
     * @return string
     * */
	public function get_method() : string{
		return $this->method;
	}

    /**
     * Get http message code
     * 
     * @return int
     * */
	public function get_code() : int{
		return $this->code;
	}

    /**
     * Get http message description
     * 
     * @return string
     * */
	public function get_status_message() : string{
		return $this->status_message;
	}

    /**
     * Get custom http message description
     * 
     * @return string
     * */
	public function get_message() : string{
		return $this->message;
	}

    /**
     * Get http message data
     * 
     * @return mixed
     * */
	public function get_response(){
		return $this->response;
	}

	private function set_status_message(){
		 $http_status_code = [
	        200 => 'Success',
	        400 => 'Bad Request',
	        401 => 'Unauthorized',
	        403 => 'Forbidden',
	        404 => 'Not Found',
	        405 => 'Method Not Allowed',
	        406 => 'Not Acceptable – You requested a format that isn’t json',
	        429 => 'Too Many Requests – You’re requesting too many kittens! Slow down!',
	        500 => 'Internal Server Error – We had a problem with our server. Try again later.',
	        503 => 'Service Unavailable – We’re temporarily offline for maintenance. Please try again later.'
	     ];
	     $this->status_message = $http_status_code[$this->code];
	}

    /**
     * Convert a feebback status to the equivalent http status code
     * */
	private static function feedbackstatus_to_statuscode($status){
		 return match($status){
             FeedBack::INVALID_INPUT, FeedBack::GENERAL_ERROR => StatusCode::BAD_REQUEST,
             FeedBack::DB_ERROR                               => StatusCode::NOT_FOUND,
             FeedBack::SUCCESS                                => StatusCode::OK,
             default                                          => StatusCode::INTERNAL_SERVER_ERROR
         };
	 }

    /**
     * Construct a http message object from a feedback object
     * 
     * @param array $feedback
     * */
	public static function from_feedback(array $feedback){
		 $http_status_code = self::feedbackstatus_to_statuscode($feedback['status']);
		 $message = $feedback['message'] ?? "";
		 $response = $feedback['feedback'];
		 return new self($http_status_code, $response, $message);
	}
}
?>