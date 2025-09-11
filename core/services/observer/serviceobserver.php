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
     //an array of observers to notify before a service method is called
	 static protected array $_before = [];

     //an array of observers to notify after a service method is called
     static protected array $_after = [];

     /**
      * The before observers are run just before a service method is called.
      * 
      * @param array $service_observers - this is a kay => value array where,
      * 
      * Key - a string composed of the service class and the service method in the format: ServiceClass@methodName
      * Value - an array of observers, each in the format: ObserverClass@methodName
      * */
     static public function before(array $service_observers){
         foreach($service_observers as $service => $observers){
             self::$_before[$service] = array_key_exists($service, self::$_before) ? array_merge(self::$_before[$service], $observers) : $observers;
         }
     }

     /**
      * The after observers are run just after a service method is called.
      * 
      * @param array $service_observers - this is a kay => value array where,
      * 
      * Key - a string composed of the service class and the service method in the format: ServiceClass@methodName
      * Value - an array of observers, each in the format: ObserverClass@methodName
      * */
     static public function after(array $service_observers){
         foreach($service_observers as $service => $observers){
             self::$_after[$service] = array_key_exists($service, self::$_after) ? array_merge(self::$_after[$service], $observers) : $observers;
         }
     }

     static public function get_service_observers(string $when, string $serviceclass, string $method){
         $key = $serviceclass."@".$method;

         return match($when){
             'before' => self::$_before[$key] ?? [],
             'after'  => self::$_after[$key] ?? []
         };
     }
}

