<?php
namespace SaQle\Http\Response\Types;

class JsonResponse{
     protected $data;
     protected $status;

     public function __construct($data, $status = 200){
         $this->data = $data;
         $this->status = $status;
     }

     public function send(){
         http_response_code($this->status);
         header('Content-Type: application/json; charset=UTF-8');
         echo json_encode($this->data);
     }
}
?>