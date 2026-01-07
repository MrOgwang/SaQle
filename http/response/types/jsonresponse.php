<?php
namespace SaQle\Http\Response\Types;

use SaQle\Http\Response\HttpResponse;

class JsonResponse extends HttpResponse{
     public function __construct(
         private mixed $data,
         private int $status
     ){}

     public function send() : void{
         http_response_code($this->status);
         header('Content-Type: application/json; charset=UTF-8');
         echo json_encode($this->data);
     }
}
