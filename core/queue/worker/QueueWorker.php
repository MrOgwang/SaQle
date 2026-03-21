<?php
namespace SaQle\Core\Queue\Worker;

use SaQle\Core\Queue\Events\QueueEvents;
use Exception;

class QueueWorker {

     protected $driver;
     protected $queue;

     public function __construct($driver, $queue = 'default') {
         $this->driver = $driver;
         $this->queue = $queue;
     }

     public function work(){
         while(true){

             $job_record = $this->driver->pop($this->queue);

             if(!$job_record){
                 sleep(2);
                 continue;
             }

             $payload = json_decode($job_record->payload, true);

             $job_class = $payload['job_class'];
             $job = new $job_class($payload['data']);
             $job->batch_id = $payload['batch_id'] ?? null;

             try{
                 QueueEvents::dispatch('job.before', $job);

                 $this->run_middleware($job, function($job){
                     $job->handle();
                 });

                 QueueEvents::dispatch('job.after', $job);

                 $this->driver->delete($job_record['id']);

             }catch(Exception $e){

                 if($job_record->attempts >= $job_record->max_attempts){
                     $this->driver->fail($job_record->id, $e->getMessage());
                     QueueEvents::dispatch('job.failed', $job);
                 }else{
                     $this->driver->release($job_record->id, 10);
                 }
             }
         }
     }

     protected function run_middleware($job, $core){

         $middleware = $job->middleware();

         $runner = array_reduce(
             array_reverse($middleware),
             function($next, $middleware){
                 return function($job) use ($middleware, $next){
                     return $middleware->handle($job, $next);
                 };
             },
             $core
         );

         return $runner($job);
     }
}