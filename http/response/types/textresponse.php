<?php
namespace SaQle\Http\Response\Types;

use SaQle\Http\Response\Response;

class TextResponse extends Response {
     protected $text;
     protected $status;

     public function __construct($text, $status = 200){
         $this->text = $text;
         $this->status = $status;
     }

     public function send(){
         http_response_code($this->status);
         header('Content-Type: text/plain; charset=UTF-8');
         echo $this->text;
     }
}

