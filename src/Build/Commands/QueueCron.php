<?php
namespace SaQle\Build\Commands;

use SaQle\Core\Queue\Worker\QueueWorker;

class QueueCron{

     static public function execute(){
         $queues = array_values(config('queue.routing', []));

         $workers = [];

         foreach($queues as $queue_name){
             $workers[] = new QueueWorker($queue_name);
         }

         $start = time();
         $max_runtime = 50;

         while(true){

             if(time() - $start > $max_runtime){
                 echo "Cron worker exiting\n";
                 break;
             }

             foreach($workers as $worker){
                 $processed = $worker->run_once();
             }

             sleep(2);
         }
     }
}