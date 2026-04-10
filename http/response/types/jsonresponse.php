<?php

namespace SaQle\Http\Response\Types;

use SaQle\Http\Response\Response;

class JsonResponse extends Response {
     protected mixed $data;
     protected int $status;
     protected string $message;


     public function __construct(mixed $data, int $status, string $message = ""){
         $this->data   = $data;
         $this->status = $status;
         $this->message = $message;
     }

     public function send(): void {
         http_response_code($this->status);
         header('Content-Type: application/json; charset=UTF-8');

         echo json_encode(
             $this->build_payload(),
             JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
         );
     }

     //Build standardized JSON payload
     protected function build_payload(): array {
         $success = $this->is_successful($this->status);

         return [
             'success' => $success,
             'status'  => $this->status,
             'message' => $this->message,
             'data'    => $success ? $this->data : null,
             'errors'  => $success ? null : $this->data,
             'meta'    => (Object)[]
         ];
     }

     //Determine success from HTTP status code
     protected function is_successful(int $status): bool {
         return $status >= 200 && $status < 300;
     }
}
