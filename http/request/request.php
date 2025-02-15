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
 * The request object
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Http\Request;

use SaQle\Http\Request\Data\Data;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Routes\Route;
use SaQle\Auth\Models\User;

class Request implements MiddlewareRequestInterface{
     /**
      * Only one instance of a request will be available
      * */
	 private static $instance;

	 public ?Data   $data                = null;
     public         $user                = null;
     public ?Route  $route               = null;
     public ?array  $trail               = null;
     public bool    $enforce_permissions = false;
	 private function __construct(){
         $this->data = new Data();
     }
     private function __clone(){}
     public function __wakeup(){}
     public static function init(){
         if(self::$instance === null){
             self::$instance = new self();
         }
         return self::$instance;
     }

     /**
      * Is this an api request. This is really just a basic test that relies
      * on whether a url has api prefixes or not
      * 
      * @return bool
      * */
     public function is_api_request() : bool{
         $is_api_request = false;
         for($u = 0; $u < count(API_URL_PREFIXES); $u++){
             if(str_contains($_SERVER['REQUEST_URI'], API_URL_PREFIXES[$u])){
                 $is_api_request = true;
                 break;
             }
         }

         return $is_api_request;
     }

     /**
      * Is this an ajax request. Ajax requests are api requests
      * sent from the same origin as the backend
      * 
      * @return bool
      * */
     public function is_ajax_request() : bool{
         $origin = $_SERVER['HTTP_ORIGIN'] ?? null;
         $referer = $_SERVER['HTTP_REFERER'] ?? null;
         $host = $_SERVER['HTTP_HOST'];

         if( (($origin && parse_url($origin, PHP_URL_HOST) === $host) || ($referer && parse_url($referer, PHP_URL_HOST) === $host)) && $this->is_api_request()){
             return true;
         }

         return false;
     }

     /**
      * Is this an sse request. This is really just a basic test that relies on whether
      * a url has sse preixes or not
      * 
      * @return bool
      * */
     public function is_sse_request() : bool{
        $is_sse_request = false;
        for($u = 0; $u < count(SSE_URL_PREFIXES); $u++){
            if(str_contains($_SERVER['REQUEST_URI'], SSE_URL_PREFIXES[$u])){
                $is_sse_request = true;
                break;
            }
        }
        return $is_sse_request;
     }
}
?>