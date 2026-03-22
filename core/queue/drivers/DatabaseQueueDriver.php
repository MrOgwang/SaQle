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

         return Db::transaction(function(){
             $select_sql = "SELECT * FROM jobs 
                     WHERE queue = ? AND available_at <= ? AND (reserved_at IS NULL OR reserved_at <= ?)
                     ORDER BY priority DESC, id ASC
                     LIMIT 1
                     FOR UPDATE
             ";

             $job = Job::run($select_sql, "select", [$queue, time(), time() - $this->visibility_timeout], false)->now();

             if($job){
                 $update_sql = "UPDATE jobs SET reserved_at = ?, attempts = attempts + 1 WHERE id = ?";
                 Job::run($update_sql, "update", [time(), $job->id])->now();
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