<?php

namespace SaQle\Core\Queue\Manager;

use SaQle\Core\Queue\Jobs\Job;
use SaQle\Core\Queue\Drivers\QueueDriverInterface;

/*class QueueManager {

     public function __construct(
         protected QueueDriverInterface $driver
     ){}

     public function dispatch(Job $job, array $options = []): void {

         $this->driver->push($job, [
            'queue'    => $options['queue'] ?? 'default',
            'delay'    => $job->get_delay(),
            'priority' => $job->get_priority(),
            'tries'    => $job->get_tries()
         ]);

     }
}*/

class QueueManager {

     protected $driver;

     public function __construct(QueueDriverInterface $driver){
         $this->driver = $driver;
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