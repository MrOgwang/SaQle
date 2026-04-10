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

use SaQle\Http\Request\Data\{Session, Data};
use SaQle\Routes\MatchedRoute;
use SaQle\Auth\Models\BaseUser;
use SaQle\Http\Request\RequestScope;
use SaQle\Http\Response\ResponseType;
use Closure;

class Request {
     //only one instance of a request will be available
	 private static $instance;

     //a wrapper around superglobals $_POST, $_GET and $_PATCH data
     public ?Data $data = null {
         set(?Data $value){
             $this->data = $value;
         }

         get => $this->data;
     }

     public private(set) ?Session $session = null {
         set(?Session $value){
             $this->session = $value;
         }

         get => $this->session;
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

     //path queries
     public ?Data $queries = null {
         set(?Data $value){
             $this->queries = $value;
         }

         get => $this->queries;
     }

     //path params
     public ?Data $params = null {
         set(?Data $value){
             $this->params = $value;
         }

         get => $this->params;
     }

     //the user who is currently logged in
     public ?BaseUser $user {
         get {
            return $this->session->get('user', null);
         }
     }

     //the selected route object
     public ?MatchedRoute $route = null {
         set(?MatchedRoute $value){
             $this->route = $value;
         }

         get => $this->route;
     }

     public ResponseType $responsetype = ResponseType::HTML {
         set(ResponseType $value){
             $this->responsetype = $value;
         }

         get => $this->responsetype;
     }

     //prevent direct creation of request object
	 private function __construct(){
         $this->data    = new Data();
         $this->headers = new Data();
         $this->cookies = new Data();
         $this->queries = new Data();
         $this->params  = new Data();
         $this->session = new Session($this);
     }

     //prevent cloning and serialization of request object
     private function __clone(){}
     public function __wakeup(){}

     //initialize a new request object
     public static function init() : Request {
         return self::$instance ??= new self();
     }

     public static function get() : Request {
         return self::$instance;
     }

     //helper functions

     public function add_context(string $name, mixed $value, bool $session = false){
         $this->session->set($name, $value, $session);
     }

     public function add_query_param(string $name, mixed $value){
         $this->queries->set($name, $value);
     }

     public function add_path_param(string $name, mixed $value){
         $this->params->set($name, $value);
     }

     public function uri(){
         return $_SERVER['REQUEST_URI'] ?? '/';
     }

     public function path(){
         return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
     }

     public function method(){
         return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
     }

     public function header(string $name): ?string {
         return $_SERVER['HTTP_'.strtoupper(str_replace('-', '_', $name))] ?? null;
     }

     public function accepts(string $mime): bool {
         $accept = $this->header('Accept');
         return $accept && str_contains($accept, $mime);
     }

     public function path_starts_with(array $prefixes): bool {
         $path = $this->path();
         foreach ($prefixes as $prefix) {
             if (str_starts_with($path, rtrim($prefix, '/'))) {
                 return true;
             }
         }
         return false;
     }

     //request scope helpers

     public function is_api_request() : bool {
         return $this->route->scope === RequestScope::API;
     }

     public function is_web_request() : bool {
         return $this->route->scope === RequestScope::WEB;
     }

     //response type helpers

     public function expects_json() : bool {
         return $this->responsetype === ResponseType::JSON;
     }

     public function expects_html() : bool {
         return $this->responsetype === ResponseType::HTML;
     }

     public function expects_sse() : bool {
         return $this->responsetype === ResponseType::SSE;
     }

     public function expects_redirect() : bool {
         return $this->responsetype === ResponseType::REDIRECT;
     }

     public function expects_file() : bool {
         return $this->responsetype === ResponseType::FILE;
     }


     public function session() : Session {
         return $this->session;
     }
}
