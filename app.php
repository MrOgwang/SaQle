<?php
namespace SaQle;

use SaQle\Autoloader;
use SaQle\Http\Request\{Request, RequestManager};
use SaQle\Templates\Static\AppStatic;
use SaQle\Config\AppConfig;
use SaQle\Middleware\AppMiddleware;
use SaQle\Templates\Context\AppContext;
use SaQle\Templates\Meta\AppMeta;
use SaQle\Controllers\Refs\ControllerRef;
use SaQle\Http\Cors\AppCors;
use SaQle\Core\Services\Container\AppContainer;
use SaQle\Core\Services\Providers\DefaultServiceProvider;

class App{
     private static ?self $instance = null;

     private static Autoloader    $_autoloader;
     private static AppStatic     $_appstatic;
     private static string        $_environment;
     private static AppConfig     $_appconfig;
     private static AppMiddleware $_appmiddleware;
     private static AppContext    $_appcontext;
     private static AppMeta       $_appmeta;
     private static ControllerRef $_appcontrollers;
     private static AppCors       $_appcors;
     private static AppContainer  $_providers;

     private function __construct(){
         self::$_autoloader     = Autoloader::init();
         self::$_appconfig      = AppConfig::init();
         self::$_appstatic      = AppStatic::init();
         self::$_appmiddleware  = AppMiddleware::init();
         self::$_appcontext     = AppContext::init();
         self::$_appmeta        = AppMeta::init();
         self::$_appcontrollers = ControllerRef::init();
         self::$_appcors        = AppCors::init();
         self::$_providers      = AppContainer::init();
     }

     public static function init(): self{
         if(self::$instance === null) {
             self::$instance = new self();
         }
         return self::$instance;
     }

     private function __clone(){}
     public function __wakeup(){}

     public static function autoloader(){
         return self::$_autoloader;
     }

     public static function static(){
         return self::$_appstatic;
     }

     public static function meta(){
         return self::$_appmeta;
     }

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

         //register and load providers
         self::$_providers::register([DefaultServiceProvider::class]);
         self::$_providers::load();
     }
}
