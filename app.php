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

class App{
     private static Autoloader    $_autoloader;
     private static AppStatic     $_appstatic;
     private static string        $_environment;
     private static AppConfig     $_appconfig;
     private static AppMiddleware $_appmiddleware;
     private static AppContext    $_appcontext;
     private static AppMeta       $_appmeta;
     private static ControllerRef $_appcontrollers;

     public function __construct(){
         self::$_autoloader     = Autoloader::init();
         self::$_appconfig      = AppConfig::init();
         self::$_appstatic      = AppStatic::init();
         self::$_appmiddleware  = AppMiddleware::init();
         self::$_appcontext     = AppContext::init();
         self::$_appmeta        = AppMeta::init();
         self::$_appcontrollers = ControllerRef::init();
     }

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

     public static function run(){
         $request         = Request::init();
         $request_manager = new RequestManager($request);
         $request_manager->process();
     }
}
