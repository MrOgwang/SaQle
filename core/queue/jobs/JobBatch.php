<?php
namespace SaQle\Core\Queue\Jobs;

use SaQle\Core\Queue\Models\JobBatch as JobBatchModel;

class JobBatch{

     protected $id;

     public function __construct($pdo){
         $this->id = uniqid('batch_');
     }

     public function dispatch($jobs, $queue_manager){

         $total = count($jobs);

         JobBatchModel::create([
             'id' => $this->id,
             'total_jobs' => $total,
             'pending_jobs' => $total,
             'failed_jobs' => 0,
             'created_at' => time()
         ])->now();

         foreach($jobs as $job){
             $job->batch_id = $this->id;
             $queue_manager->dispatch($job);
         }
     }
}