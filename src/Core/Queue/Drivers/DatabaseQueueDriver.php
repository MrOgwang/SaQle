<?php
namespace SaQle\Core\Queue\Drivers;

use SaQle\Core\Queue\Models\{Job, FailedJob};
use SaQle\Core\Support\Db;

class DatabaseQueueDriver implements QueueDriverInterface {
    
     protected $visibility_timeout = 60;

     public function push($queue, $payload, $priority = 0, $delay = 0){
         $job = Job::create([
             'queue' => $queue,
             'payload' => json_encode($payload),
             'priority' => $priority,
             'attempts' => 0,
             'available_at' => time() + $delay,
             'created_at' => time(),
             'max_attempts' => 3
         ])->now();
     }

     public function pop($queue){

         return Db::transaction(function() use ($queue){

             $timeout = $this->visibility_timeout;

             $query = Job::get()->where('queue', $queue)->where('available_at__lte', time())
                    ->gwhere(function($q) use ($timeout) {
                         return $q->where('reserved_at', null)->or_where('reserved_at__lte', time() - $timeout);
                    })
                    ->order(['priority'], "DESC")
                    ->limit(1);

             $job = $query->first_or_null();

             if($job){
                 Job::update([
                    'reserved_at' => time(),
                    'attempts' => $job->attempts + 1
                 ])->where('id', $job->id)->now();
             }

             return $job;
         });
     }

     public function delete($job_id){
         Job::delete(true)->where('id', $job_id)->now();
     }

     public function release($job_id, $delay = 0){
         Job::update([
            'reserved_at' => null,
            'available_at' => time() + $delay
         ])->where('id', $job_id)->now();
     }

     public function fail($job_id, $exception){
         $job = FailedJob::create([
             'job_id' => $job_id,
             'exception' => $exception,
             'failed_at' => time()
         ])->now();

         $this->delete($job_id);
     }
}