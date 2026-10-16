<?php

namespace SaQle\Http\Message;

use SaQle\Http\HttpStatus;

class Message {

     //Data from controller. This is an associative array
     protected array  $data = [];

     protected string $message = '';

     public function __construct(
         protected int $code = HttpStatus::OK,
         string $message = "",
         array  $data    = []
     ){
         $this->message = $message ?: HttpStatus::reason($code);
         $this->data    = $data;
     }

     public function is_success(): bool {
         return HttpStatus::is_success($this->code);
     }

     public function is_error(): bool {
         return HttpStatus::is_error($this->code);
     }

     //general message factory
     public static function make(int $code, string $message = "") : static {
         return new static($code, $message);
     }

     public static function ok(string $message = ""): static {
         return new static(HttpStatus::OK, $message);
     }

     public static function created(string $message = ""): static {
         return new static(HttpStatus::CREATED, $message);
     }

     public static function accepted(string $message = ""): static {
         return new static(HttpStatus::ACCEPTED, $message);
     }

     public static function bad_request(string $message = ""): static {
         return new static(HttpStatus::BAD_REQUEST, $message);
     }

     public static function unauthenticated(string $message = ""): static {
         return new static(HttpStatus::UNAUTHENTICATED, $message);
     }

     public static function unauthorized(string $message = ""): static {
         return new static(HttpStatus::UNAUTHORIZED, $message);
     }

     public static function not_found(string $message = ""): static {
         return new static(HttpStatus::NOT_FOUND, $message);
     }

     public static function method_not_allowed(string $message = ""): static {
         return new static(HttpStatus::METHOD_NOT_ALLOWED, $message);
     }

     public static function unprocessable_entity(string $message = ""): static {
         return new static(HttpStatus::UNPROCESSABLE_ENTITY, $message);
     }

     public static function too_many_requests(string $message = ""): static {
         return new static(HttpStatus::TOO_MANY_REQUESTS, $message);
     }

     public static function internal_server_error(string $message = ""): static {
         return new static(HttpStatus::INTERNAL_SERVER_ERROR, $message);
     }

     public static function service_unavailable(string $message = ""): static {
         return new static(HttpStatus::SERVICE_UNAVAILABLE, $message);
     }

     public static function redirect(string $url): RedirectMessage {
         return new RedirectMessage($url);
     } 

     public static function reload(): RedirectMessage {
         return new RedirectMessage(request()->uri());
     }

     public static function file(array $file){
         return new FileMessage($file);
     }

     public function with(string $key, mixed $value): static {

         $this->data[$key] = $value;

         return $this;
     }

     public function data(array $data, bool $append = false) : static {

         $this->data = $append ? array_merge($this->data, $data) : $data;

         return $this;
     }

     public function message(string $m): static {

         $this->message = $message;

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