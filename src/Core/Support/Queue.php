<?php

namespace SaQle\Core\Support;

use SaQle\Core\Queue\Drivers\{
     QueueDriverInterface,
     DatabaseQueueDriver
};
use SaQle\Core\Queue\Utils\QueueUtils;

class Queue {

     use QueueUtils;

     protected static $driver;

     public static function driver() {
         if(!self::$driver){
             self::$driver = self::resolve_driver();
         }

         return self::$driver;
     }

     private static function get_queue_priority(string $queue_name){
         return 0;
     }

     private static function get_queue_delay(string $queue_name){
         return 0;
     }
     
     public static function dispatch(Queueable $job, ?string $queue = null){

         $queue = self::resolve_queue($job, $queue);
         $payload = self::create_payload($job);
         $priority = self::get_queue_priority($queue);
         $delay = 0;

         self::driver()->push($queue, $payload, $priority, $delay);
     }

     public static function later(int $delay, Queueable $job, ?string $queue = null){

         $queue = self::resolve_queue($job, $queue);
         $payload = self::create_payload($job);
         $priority = self::get_queue_priority($queue);

         self::driver()->push($queue, $payload, $priority, $delay);
     }

     public static function call(callable $callable, $args = [], $queue = null){

         $queue = self::resolve_queue(null, $queue);
         $payload = [
             'type' => 'callable',
             'callable' => self::serialize_callable($callable),
             'args' => $args
         ];
         $priority = self::get_queue_priority($queue);
         $delay = 0;

         self::driver()->push($queue, $payload, $priority, $delay);
     }

     public static function call_later(int $delay, callable $callable, $args = [], $queue = null){

         $queue = self::resolve_queue(null, $queue);
         $payload = [
             'type' => 'callable',
             'callable' => self::serialize_callable($callable),
             'args' => $args
         ];
         $priority = self::get_queue_priority($queue);

         self::driver()->push($queue, $payload, $priority, $delay);
     }

     public static function bulk(array $jobs, ?string $queue = null){
         foreach($jobs as $job){
             self::dispatch($job, $queue);
         }
     }

     public static function bulk_later(int $delay, array $jobs, ?string $queue = null){
         foreach($jobs as $job){
             self::later($delay, $job, $queue);
         }
     }

     public static function push_raw(array $payload, ?string $queue = null, $delay = 0){
         $queue = self::resolve_queue(null, $queue);
         $priority = self::get_queue_priority($queue);
         $delay = 0;

         self::driver()->push($queue, $payload, $priority, $delay);
     }

     public static function push_raw_later(int $delay, array $payload, ?string $queue = null){
         $queue = self::resolve_queue(null, $queue);
         $priority = self::get_queue_priority($queue);

         self::driver()->push($queue, $payload, $priority, $delay);
     }

     protected static function serialize_callable(callable $callable){

         if(is_array($callable)) {
             return [
                 'type' => 'class_method',
                 'class' => is_object($callable[0]) ? get_class($callable[0]) : $callable[0],
                 'method' => $callable[1]
             ];
         }

         if(is_string($callable)) {
             return [
                 'type' => 'function',
                 'function' => $callable
             ];
         }

         throw new Exception("Unsupported callable");
     }

     //convert jobs into storable payloads
     protected static function create_payload(Queueable $job){
         if($job instanceof Queueable){
             return [
                'type'    => 'job',
                'class'   => get_class($job),
                'payload' => $job->queue_job_payload(),
                'handler' => $job->queue_job_handler()
             ];
         }

         throw new Exception("Job must implement Queueable");
     }
}