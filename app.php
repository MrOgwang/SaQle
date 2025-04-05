<?php
namespace SaQle;

use SaQle\Http\Request\{Request, RequestManager};
use SaQle\Config\AppConfig;
use SaQle\Middleware\AppMiddleware;
use SaQle\Templates\Context\AppContext;
use SaQle\Controllers\Refs\ControllerRef;
use SaQle\Http\Cors\AppCors;
use SaQle\Core\Services\Container\AppContainer;
use SaQle\Core\Services\Providers\AppProvider;
use SaQle\Services\DefaultServiceLocator;

class App{
     private static ?self $instance = null;

     private static string        $_environment;
     private static AppConfig     $_appconfig;
     private static AppMiddleware $_appmiddleware;
     private static AppContext    $_appcontext;
     private static ControllerRef $_appcontrollers;
     private static AppCors       $_appcors;
     private static AppContainer  $_locators;
     private static AppProvider   $_providers;

     private function __construct(){
         self::$_appconfig      = AppConfig::init();
         self::$_appmiddleware  = AppMiddleware::init();
         self::$_appcontext     = AppContext::init();
         self::$_appcontrollers = ControllerRef::init();
         self::$_appcors        = AppCors::init();
         self::$_locators       = AppContainer::init();
         self::$_providers      = AppProvider::init();
     }

     public static function init(): self{
         if(self::$instance === null) {
             self::$instance = new self();
         }
         return self::$instance;
     }

     private function __clone(){}
     public function __wakeup(){}

     public static function context(){
         return self::$_appcontext;
     }

     public static function config(){
         return self::$_appconfig;
     }

     public static function middleware(){
         return self::$_appmiddleware;
     }

     public static function controllers(){
         return self::$_appcontrollers;
     }

     public static function environment(string $env){
         self::$_environment = $env;
     }

     public static function getenvironment(){
         return self::$_environment;
     }

     public static function cors(){
         return self::$_appcors;
     }

     public static function locators(){
         return self::$_locators;
     }

     public static function providers(){
         return self::$_providers;
     }

     public static function run(){
         self::bootstrap();

         //start and process request
         $request         = Request::init();
         $request_manager = new RequestManager($request);
         $request_manager->process();
     }

     public static function bootstrap(){
         //bootstrap helpers
         require_once __DIR__.'/shortcuts/helpers.php';

         //register and load locators
         self::$_locators::register([DefaultServiceLocator::class]);
         self::$_locators::load();

         //register and load providers
         self::$_providers::register([]);
         self::$_providers::load(); 
     }
}
