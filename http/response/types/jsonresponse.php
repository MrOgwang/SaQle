<?php

namespace SaQle\Http\Response\Types;

use SaQle\Http\Response\Response;

final class JsonResponse extends Response {
     public function __construct(
         protected mixed $data,
         int $status = 200,
         protected string $message = '',
         protected array $meta = [],
         array $headers = []
     ){
         parent::__construct($status, $headers);
         $this->header('Content-Type', 'application/json; charset=UTF-8');
     }

     public function set_data(mixed $data): static {
         $this->data = $data;
         return $this;
     }

     public function get_data(): mixed {
         return $this->data;
     }

     protected function send_content(): void {
         $success = $this->status >= 200 && $this->status < 300;

         echo json_encode([
            'success' => $success,
            'status' => $this->status,
            'message' => $this->message,
            'data' => $success ? $this->data : null,
            'errors' => $success ? null : $this->data,
            'meta' => (object) $this->meta,
         ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
     }
}
