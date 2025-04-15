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

use SaQle\Http\Request\Data\{RequestContext, Data};
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Routes\Route;
use SaQle\Auth\Models\BaseUser;
use Closure;

class Request implements MiddlewareRequestInterface{
     //only one instance of a request will be available
	 private static $instance;

     //a wrapper around superglobals $_POST, $_GET and $_PATCH data
     public ?Data $data = null {
         set(?Data $value){
             $this->data = $value;
         }

         get => $this->data;
     }

     public ?RequestContext $context = null {
         set(?RequestContext $value){
             $this->context = $value;
         }

         get => $this->context;
     }

     //a wrapper around request headers
     public ?Data $headers = null {
         set(?Data $value){
             $this->headers = $value;
         }

         get => $this->headers;
     }

     //a wrapper around request cookies
     public ?Data $cookies = null {
         set(?Data $value){
             $this->cookies = $value;
         }

         get => $this->cookies;
     }

     //the user who is currently logged in
     public ?BaseUser $user {
         get {
            return $this->context->get('user', null);
         }
     }

     //the selected route object
     public ?Route $route = null {
         set(?Route $value){
             $this->route = $value;
         }

         get => $this->route;
     }

     //the trail of routes for a web request
     public ?array $trail = null {
         set(?array $value){
             $this->trail = $value;
         }

         get => $this->trail;
     }
     
     //whether to enforce permissions check on this route
     public bool $enforce_permissions = false {
         set(bool $value){
             $this->enforce_permissions = $value;
         }

         get => $this->enforce_permissions;
     }

     //prevent direct creation of request object
	 private function __construct(){
         $this->data    = new Data();
         $this->headers = new Data();
         $this->cookies = new Data();
         $this->context = new RequestContext();
     }

     //prevent cloning and serialization of request object
     private function __clone(){}
     public function __wakeup(){}

     //initialize a new request object
     public static function init(){
         if(self::$instance === null)
             self::$instance = new self();
         
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
         $origin  = $_SERVER['HTTP_ORIGIN'] ?? null;
         $referer = $_SERVER['HTTP_REFERER'] ?? null;
         $host    = $_SERVER['HTTP_HOST'];

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

     public function is_web_request() : bool{
         return !$this->is_api_request() && !$this->is_ajax_request() && !$this->is_sse_request();
     }

     public function add_context(string $name, mixed $value, bool $session = false){
         $this->context->set($name, $value, $session);
     }
}
?>