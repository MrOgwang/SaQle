<?php
namespace SaQle\Http\Request;

use SaQle\Http\Request\Data\Data;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Routes\Route;
use SaQle\Auth\Models\User;

class Request implements MiddlewareRequestInterface{
	 private static $instance;
	 public ?Data   $data    = null;
     public ?Data   $session = null;
     public         $user    = null;
     public ?Route  $route   = null;
     public ?array  $trail   = null;
	 private function __construct(){
        $this->data    = new Data();
        $this->session = new Data();
     }
     public static function init(){
         if(self::$instance === null){
             self::$instance = new self();
         }
         return self::$instance;
     }
}
?>