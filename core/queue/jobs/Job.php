<?php
namespace SaQle\Core\Queue\Jobs;

abstract class Job implements JobInterface {

     public $data = [];

     public $batch_id = null;

     public function __construct($data = []){
         $this->data = $data;
     }

     public function middleware(){
         return [];
     }
}