<?php
namespace SaQle\Build\Commands;

class RunCron {
     static public function execute(){
         log_to_file("Running cron!");
     }
}
