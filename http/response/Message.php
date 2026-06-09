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
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */

namespace SaQle\Http\Response;

use SaQle\Core\FeedBack\FeedBack;
use SaQle\Http\Request\Data\Data;

class Message extends FeedBack {

     private ?Data $_flash = null;

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
	 public function __construct(int $code, mixed $response = null, string $message = ""){
		 $this->set($code, $response, $message);
		 $this->status_message = $this->get_message($this->code);
	 }

	 public function set_data(array $data){
		 $this->data = $data;
	 }

     public function set_code(int $code){
         $this->code = $code;
     }

     /**
     * Construct a http message object from a feedback object
     * 
     * @param array $feedback
     * */
	 public static function from_feedback(FeedBack $fb){
		 return new self($fb->code, $fb->data, $fb->message);
	 }

     //session flashing
     public function flash(string $key, mixed $data = null){
         if(!$this->_flash){
             $this->_flash = new Data();
         }

         $this->_flash->set($key, $data);
     } 

     public function should_flash() : bool {
         return !is_null($this->_flash);
     }

	 //http message helpers

     public static function status(mixed $data = null, string $message = ''){
         return new InformationMessage($message, $data);
     }

     public static function ok(mixed $data = null, string $message = ''){
         return new SuccessMessage($message, $data);
     }

     public static function redirect(string $url){
         return new RedirectMessage($url);
     }

     public static function file(array $file_info){
         return new FileMessage($file_info);
     }

     public static function multiple_choices(mixed $data = null, string $message = ''){
         return new static(self::MULTIPLE_CHOICES, $data, $message);
     }

     public static function not_modified(mixed $data = null, string $message = ''){
         return new static(self::NOT_MODIFIED, $data, $message);
     }

     public static function bad_request(mixed $data = null, string $message = ''){
         return new static(self::BAD_REQUEST, $data, $message);
     }

     public static function unauthenticated(mixed $data = null, string $message = ''){
         return new static(self::UNAUTHENTICATED, $data, $message);
     }

     public static function unauthorized(mixed $data = null, string $message = ''){
         return new static(self::UNAUTHORIZED, $data, $message);
     }

     public static function payment_required(mixed $data = null, string $message = ''){
         return new static(self::PAYMENT_REQUIRED, $data, $message);
     }

     public static function not_found(mixed $data = null, string $message = ''){
         return new static(self::NOT_FOUND, $data, $message);
     }

     public static function method_not_allowed(mixed $data = null, string $message = ''){
         return new static(self::METHOD_NOT_ALLOWED, $data, $message);
     }

     public static function not_acceptable(mixed $data = null, string $message = ''){
         return new static(self::NOT_ACCEPTABLE, $data, $message);
     }

     public static function request_timeout(mixed $data = null, string $message = ''){
         return new static(self::REQUEST_TIMEOUT, $data, $message);
     }

     public static function conflict(mixed $data = null, string $message = ''){
         return new static(self::CONFLICT, $data, $message);
     }

     public static function too_many_requests(mixed $data = null, string $message = ''){
         return new static(self::TOO_MANY_REQUESTS, $data, $message);
     }

     public static function proxy_authentication_required(mixed $data = null, string $message = ''){
         return new static(self::PROXY_AUTHENTICATION_REQUIRED, $data, $message);
     }

     public static function gone(mixed $data = null, string $message = ''){
         return new static(self::GONE, $data, $message);
     }
     
     public static function length_required(mixed $data = null, string $message = ''){
         return new static(self::LENGTH_REQUIRED, $data, $message);
     }
     
     public static function precondition_failed(mixed $data = null, string $message = ''){
         return new static(self::PRECONDITION_FAILED, $data, $message);
     }
     
     public static function content_too_large(mixed $data = null, string $message = ''){
         return new static(self::CONTENT_TOO_LARGE, $data, $message);
     }
     
     public static function uri_too_long(mixed $data = null, string $message = ''){
         return new static(self::URI_TOO_LONG, $data, $message);
     }
     
     public static function unsupported_media_type(mixed $data = null, string $message = ''){
         return new static(self::UNSUPPORTED_MEDIA_TYPE, $data, $message);
     }
     
     public static function range_not_satisfiable(mixed $data = null, string $message = ''){
         return new static(self::RANGE_NOT_SATISFIABLE, $data, $message);
     }
     
     public static function expectation_failed(mixed $data = null, string $message = ''){
         return new static(self::EXPECTATION_FAILED, $data, $message);
     }
     
     public static function im_a_teapot(mixed $data = null, string $message = ''){
         return new static(self::IM_A_TEAPOT, $data, $message);
     }
     
     public static function misdirected_request(mixed $data = null, string $message = ''){
         return new static(self::MISDIRECTED_REQUEST, $data, $message);
     }
     
     public static function unprocessable_entity(mixed $data = null, string $message = ''){
         return new static(self::UNPROCESSABLE_ENTITY, $data, $message);
     }
     
     public static function locked(mixed $data = null, string $message = ''){
         return new static(self::LOCKED, $data, $message);
     }
     
     public static function failed_dependency(mixed $data = null, string $message = ''){
         return new static(self::FAILED_DEPENDENCY, $data, $message);
     }
     
     public static function too_early(mixed $data = null, string $message = ''){
         return new static(self::TOO_EARLY, $data, $message);
     }
     
     public static function upgrade_required(mixed $data = null, string $message = ''){
         return new static(self::UPGRADE_REQUIRED, $data, $message);
     }
     
     public static function precondition_required(mixed $data = null, string $message = ''){
         return new static(self::PRECONDITION_REQUIRED, $data, $message);
     }
     
     public static function request_header_fields_too_large(mixed $data = null, string $message = ''){
         return new static(self::REQUEST_HEADER_FIELDS_TOO_LARGE, $data, $message);
     }
     
     public static function unavailable_for_legal_reasons(mixed $data = null, string $message = ''){
         return new static(self::UNAVAILABLE_FOR_LEGAL_REASONS, $data, $message);
     }


     public static function internal_server_error(mixed $data = null, string $message = ''){
         return new static(self::INTERNAL_SERVER_ERROR, $data, $message);
     }

     public static  function service_unavailable(mixed $data = null, string $message = ''){
         return new static(self::SERVICE_UNAVAILABLE, $data, $message);
     }

     public static  function method_not_implemented(mixed $data = null, string $message = ''){
         return new static(self::METHOD_NOT_IMPLEMENTED, $data, $message);
     }
     
     public static  function bad_gateway(mixed $data = null, string $message = ''){
         return new static(self::BAD_GATEWAY, $data, $message);
     }

     public static  function gateway_timeout(mixed $data = null, string $message = ''){
         return new static(self::GATEWAY_TIMEOUT, $data, $message);
     }
     
     public static  function unsupported_http_version(mixed $data = null, string $message = ''){
         return new static(self::UNSUPPORTED_HTTP_VERSION, $data, $message);
     }
     
     public static  function variant_also_negotiates(mixed $data = null, string $message = ''){
         return new static(self::VARIANT_ALSO_NEGOTIATES, $data, $message);
     }
     
     public static  function insufficient_storage(mixed $data = null, string $message = ''){
         return new static(self::INSUFFICIENT_STORAGE, $data, $message);
     }
     
     public static  function loop_detected(mixed $data = null, string $message = ''){
         return new static(self::LOOP_DETECTED, $data, $message);
     }
     
     public static  function not_extended(mixed $data = null, string $message = ''){
         return new static(self::NOT_EXTENDED, $data, $message);
     }
     
     public static  function network_authentication_required(mixed $data = null, string $message = ''){
         return new static(self::NETWORK_AUTHENTICATION_REQUIRED, $data, $message);
     }
}
