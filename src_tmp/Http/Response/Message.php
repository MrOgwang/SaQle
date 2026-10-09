
<?php

namespace SaQle\Http\Response;

use SaQle\Http\HttpStatus;

class Message {

     //Data from controller. This is an associative array
     private array  $data = [];

     private string $message = '';

     public function __construct(
         private int $code = HttpStatus::OK,
         string $message = ""
     ){
         $this->message = $message ?: HttpStatus::reason($code);
     }

     public static function make(int $code, string $message = "") : static {
         return new static($code, $message);
     }

     public static function ok(): static {
         return new static(HttpStatus::OK);
     }

     public static function created(): static {
         return new static(HttpStatus::CREATED);
     }

     public static function accepted(): static {
         return new static(HttpStatus::ACCEPTED);
     }

     public static function bad_request(): static {
         return new static(HttpStatus::BAD_REQUEST);
     }

     public static function unauthenticated(): static {
         return new static(HttpStatus::UNAUTHENTICATED);
     }

     public static function unauthorized(): static {
         return new static(HttpStatus::UNAUTHORIZED);
     }

     public static function not_found(): static {
         return new static(HttpStatus::NOT_FOUND);
     }

     public static function method_not_allowed(): static {
         return new static(HttpStatus::METHOD_NOT_ALLOWED);
     }

     public static function unprocessable_entity(): static {
         return new static(HttpStatus::UNPROCESSABLE_ENTITY);
     }

     public static function too_many_requests(): static {
         return new static(HttpStatus::TOO_MANY_REQUESTS);
     }

     public static function internal_server_error(): static {
         return new static(HttpStatus::INTERNAL_SERVER_ERROR);
     }

     public static function service_unavailable(): static {
         return new static(HttpStatus::SERVICE_UNAVAILABLE);
     }

     public static function redirect(string $url): RedirectMessage {
         return new RedirectMessage($url);
     }

     public static function reload(): RedirectMessage {
         return new RedirectMessage($url);
     }

     public static function file(array $file_info){
         return new FileMessage($file_info);
     }

     public function with(string $key, mixed $value): static {

         $this->data[$key] = $value;

         return $this;
     }

     public function get_code(): int {
         return $this->code;
     }

     public function get_data(): array {
         return $this->data;
     }

     public function get_message(): string {
         return $this->message;
     }
}