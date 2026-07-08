<?php

namespace SaQle\Http\Response;

class SuccessMessage extends Message {

	 public function __construct(string $message = "", mixed $data = null){
	 	 parent::__construct(self::OK, $data, $message);
	 }

	 public function created(){
	 	 $this->code = self::CREATED;
     }

     public function accepted(){
         $this->code = self::ACCEPTED;
     }

     public function non_authoritative_info(){
         $this->code = self::NON_AUTHORITATIVE_INFO;
     }

     public function no_content(){
         $this->code = self::NO_CONTENT;
     }

     public function reset_content(){
         $this->code = self::RESET_CONTENT;
     }

     public function partial_content(string $message = '', mixed $data = null){
         $this->code = self::PARTIAL_CONTENT;
     }

     public function multi_status(string $message = '', mixed $data = null){
         $this->code = self::MULTI_STATUS;
     }

     public function already_reported(string $message = '', mixed $data = null){
         $this->code = self::ALREADY_REPORTED;
     }

     public function im_used(string $message = '', mixed $data = null){
         $this->code = self::IM_USED;
     }

}