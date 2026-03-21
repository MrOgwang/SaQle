<?php
namespace SaQle\Core\Queue\Jobs;

class QueuedJob {
     public function __construct(
         public string $id,
         public Job    $job,
         public int    $attempts = 0
     ){}
}