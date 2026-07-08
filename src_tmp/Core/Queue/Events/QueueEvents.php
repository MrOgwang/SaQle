<?php
namespace SaQle\Core\Queue\Events;

class QueueEvents {

     protected static $listeners = [];

     public static function listen($event, $callback){
         self::$listeners[$event][] = $callback;
     }

     public static function dispatch($event, $payload = null){
         if(!isset(self::$listeners[$event])) return;

         foreach(self::$listeners[$event] as $listener){
             $listener($payload);
         }
     }
}