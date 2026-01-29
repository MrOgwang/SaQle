<?php
namespace SaQle\Build\Commands;

class StartApps{
     private function start_apps(string $project_root, $name){
         echo "Starting apps! {$name}\n";
     }

     public function execute($project_root, $name){
           $this->start_apps($project_root, $name);
     }
}
