<?php
namespace SaQle\Core\Queue\Worker;

use SaQle\Core\Queue\Events\QueueEvents;
use SaQle\Core\Queue\Utils\QueueUtils;
use Exception;

class QueueWorker {
     use QueueUtils;

     protected $driver;

     protected $queue;

     public function __construct($queue = null){
         $this->driver = self::resolve_driver();
         $this->queue = self::resolve_queue(null, $queue);
     }

     private function process_job(array $payload){

         $job_class          = $payload['class'];
         $data               = $payload['payload'];
         $handler            = array_keys($payload['handler'])[0];
         $handler_params     = [];

         foreach(array_values($payload['handler'])[0] as $arg){
             if(array_key_exists($arg, $data)){
                 $handler_params[$arg] = $data[$arg];
             }
         }
       
         $constructor_params = array_diff_key($data, $handler_params);

         $job = $job_class::init_queue_job($constructor_params);

         QueueEvents::dispatch('job.before', $job);

         $this->run_middleware($job, function($job) use ($handler, $handler_params) {
             $job->$handler(...$handler_params);
         });

         QueueEvents::dispatch('job.after', $job);

     }

     private function process_callable(array $payload){

     }

     public function run_once(){
         $job_record = $this->driver->pop($this->queue);

         if(!$job_record){
             return false;
         }

         $payload = json_decode($job_record->payload, true);

         try{
             
             if($payload['type'] === 'job'){
                 $this->process_job($payload);
             }else{
                 $this->process_callable($payload);
             }

             $this->driver->delete($job_record->id);

         }catch(Exception $e){
             if($job_record->attempts >= $job_record->max_attempts){
                 $this->driver->fail($job_record->id, $e->getMessage());
                 QueueEvents::dispatch('job.failed', $job);
             }else{
                $this->driver->release($job_record->id, 10);
             }
         }

         return true;
     }

     public function work(){
         while(true){
             $processed = $this->run_once();

             if(!$processed){
                 sleep(2);
             }
         }
     }

     protected function run_middleware($job, $core){

         $middleware = $job->queue_job_middleware();

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