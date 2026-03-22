<?php

namespace SaQle\Core\Queue\Manager;

use SaQle\Core\Queue\Jobs\Job;
use SaQle\Core\Queue\Drivers\{
     QueueDriverInterface,
     DatabaseQueueDriver
};

class QueueManager {

     protected $driver;

     public function __construct(){
         $this->driver = $this->resolve_driver();
     }

     protected function resolve_driver() : QueueDriverInterface {
         $driver = config('queue.driver');

         return match($driver){
             'db' => new DatabaseQueueDriver(),
             default => new DatabaseQueueDriver()
         };
     }

     public function dispatch(Job $job, $queue = 'default', $priority = 0, $delay = 0){

         $payload = [
             'job_class' => get_class($job),
             'data' => $job->data,
             'batch_id' => $job->batch_id
         ];

         $this->driver->push($queue, $payload, $priority, $delay);
     }
}