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
 * The service observer class is used to add observer classes to services when initializing the app.
 * 
 * This class is used together with the ServiceObserversProvider.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com><+254741142038>
 * */
namespace SaQle\Core\Services\Observer;

class ServiceObserver {
	 static protected array $_before     = [];
     static protected array $_before_all = [];
     static protected array $_after      = [];
     static protected array $_after_all  = [];

     /**
      * The before observers are run just before a service method is called.
      * 
      * 1. If a serviceclass is not provided, these observers will be applied to all services that implement IService
      * 2. If the serviceclass is provided but the methodname is not, the observers will be applied to all
      *    the methods defined on a service
      * */
     static public function before(array | string $observerclass, ?string $serviceclass = null, ?string $methodname = null){
         $observerclass = is_array($observerclass) ? $observerclass : [$observerclass];
         
         if(!$serviceclass){
             self::$_before_all = array_merge(self::$_before_all, $observerclass);
         }else{
             $methodname = $methodname ?? '__';
             self::$_before[$serviceclass] = self::$_before[$serviceclass] ?? [];
             self::$_before[$serviceclass][$methodname] = self::$_before[$serviceclass][$methodname] ?? [];
             self::$_before[$serviceclass][$methodname] = array_merge(self::$_before[$serviceclass][$methodname], $observerclass);
         }
     }

     static public function after(array | string $observerclass, ?string $serviceclass = null, ?string $methodname = null){
         $observerclass = is_array($observerclass) ? $observerclass : [$observerclass];
         
         if(!$serviceclass){
             self::$_after_all = array_merge(self::$_after_all, $observerclass);
         }else{
             $methodname = $methodname ?? '__';
             self::$_after[$serviceclass] = self::$_after[$serviceclass] ?? [];
             self::$_after[$serviceclass][$methodname] = self::$_after[$serviceclass][$methodname] ?? [];
             self::$_after[$serviceclass][$methodname] = array_merge(self::$_after[$serviceclass][$methodname], $observerclass);
         }
     }

     static public function get_service_observers(string $when, string $serviceclass, ?string $method = null){
         $observers = match($when){
             'before' => self::$_before[$serviceclass] ?? [],
             'after'  => self::$_after[$serviceclass] ?? []
         };

         return $method ? ($observers[$method] ?? []) : $observers;
     }

     static public function get_shared_observers(string $when){
         return match($when){
             'before' => self::$_before_all,
             'after'  => self::$_after_all
         };
     }
}

?>