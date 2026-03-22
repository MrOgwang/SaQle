<?php
namespace SaQle\Build\Commands;

class QueueCron {

     static public function execute(){
         log_to_file("Running cron!");
     }

     public function handle(){

        $queues = config('queue.queues');

        $start = time();
        $max_runtime = 50;

        while(true) {

            if(time() - $start > $max_runtime) {
                echo "Cron worker exiting\n";
                break;
            }

            foreach($queues as $queue_name => $config) {

                $worker = new QueueWorker(
                    Queue::driver(),
                    $queue_name
                );

                $worker->run_once();
            }

            sleep(2);
        }
     }
}