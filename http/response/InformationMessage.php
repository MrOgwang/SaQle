<?php

namespace SaQle\Http\Response;

class InformationMessage extends Message {

	 public function __construct(string $message = "", mixed $data = null){
	 	 parent::__construct(self::PROCESSING, $data, $message);
	 }

	 public function continue(){
         $this->code = self::CONTINUE;
     }

     public function switching_protocals(){
         $this->code = self::SWITCHING_PROTOCALS;
     }

     public function processing(){
         $this->code = self::PROCESSING;
     }

     public function early_hints(){
         $this->code = self::EARLY_HINTS;
     }

}