<?php
namespace SaQle\Build\Commands;

class StartProject{
     private function start_project($name){
         echo "Starting project! {$name}\n";
     }

     public function execute($name){
         $this->start_project(...[
             'name' => $name
         ]);
     }
}
