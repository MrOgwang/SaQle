<?php

namespace SaQle\Core\Queue\Utils;

use SaQle\Core\Queue\Drivers\{
     QueueDriverInterface,
     DatabaseQueueDriver
};

trait QueueUtils {
	 protected static function resolve_driver() : QueueDriverInterface {
         $driver = config('queue.driver');

         return match($driver){
             'db' => new DatabaseQueueDriver(),
             default => new DatabaseQueueDriver()
         };
     }

     protected static function resolve_queue($job = null, $queue = null){

         if($queue){
             return $queue;
         }

         if($job){
             if(method_exists($job, 'default_queue')) {
                 return $job->default_queue();
             }

             if(property_exists($job, 'queue')) {
                 return $job->queue;
             }
         }
         
         return config('queue.default');
     }

}