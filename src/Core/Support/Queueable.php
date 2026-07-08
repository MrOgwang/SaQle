<?php

/**
 * Turn any class into a queue job by using this interface
 * and implementing the methods herein.
 * */

namespace SaQle\Core\Support;

interface Queueable {

     /**
      * Return the name of the class method that will be used
      * as the job handler.
      * 
      * This is a key => value array with key as the method name
      * and value as an array of params to pass to the method
      * from the payload.
      * 
      * ['method_name' => ['param_1', 'param_2']]
      * */
	 public function queue_job_handler() : array;

     /**
      * Return a key => value array of the data that will be stored
      * and used with the job later
      * */
	 public function queue_job_payload() : array;

     /**
      * Return an array of middleware to run before executing
      * a queue job
      * */
	 public function queue_job_middleware() : array;

	 /**
	  * Reconstruct a new Queueable instance from the payload
	  * */
	 public static function init_queue_job(array $data) : Queueable;

	 /**
	  * Return the default queue name to use
	  * */
	 public function default_queue() : string;
}