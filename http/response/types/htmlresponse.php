<?php
namespace SaQle\Http\Response\Types;

use SaQle\Http\Response\HttpResponse;

class HtmlResponse extends HttpResponse {
     public function __construct(
         private string $html, 
         private int $status = 200
     ){}

     public function send() : void {
         http_response_code($this->status);
         header('Content-Type: text/html; charset=UTF-8');
         echo $this->html;
     }
}
