<?php
namespace SaQle\Http\Response\Types;

class HtmlResponse{
     protected $content;
     protected $status;

     public function __construct($content, $status = 200){
         $this->content = $content;
         $this->status = $status;
     }

     public function send(){
         http_response_code($this->status);
         header('Content-Type: text/html; charset=UTF-8');
         echo $this->content;
     }
}
?>