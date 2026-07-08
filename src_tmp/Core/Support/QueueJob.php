<?php

namespace SaQle\Core\Support;

trait QueueJob {
     public function queue_job_handler() : array {
        return ['handle' => []];
     }

     public function queue_job_payload() : array {
         return [];
     }

     public function queue_job_middleware() : array {
         return [];
     }

     public static function init_queue_job(array $data) : Queueable {
         return new static();
     }

     public function default_queue() : string {
         return config('queue.default');
     }
}