<?php

declare(strict_types=1);

namespace SaQle\Http\Response;

use RuntimeException;

abstract class Response {
     protected int $status = 200;
     protected array $headers = [];
     protected array $cookies = [];
     protected string $protocol_version = '1.1';
     protected bool $sent = false;

     public function __construct(int $status = 200, array $headers = []){
         $this->set_status($status);

         foreach($headers as $name => $value){
             $this->header($name, $value);
         }
     }

     public function set_status(int $status): static {
         if($status < 100 || $status > 599){
             throw new RuntimeException("Invalid HTTP status code: {$status}");
         }

         $this->status = $status;

         return $this;
     }

     public function get_status(): int {
         return $this->status;
     }

     public function header(string $name, string $value, bool $replace = true): static {
         $normalized = $this->normalize_header_name($name);

         if($replace || !isset($this->headers[$normalized])){
             $this->headers[$normalized] = [$value];
         }else{
             $this->headers[$normalized][] = $value;
         }

         return $this;
     }

     public function headers(array $headers): static {
         foreach($headers as $name => $value){
             if(is_array($value)){
                 foreach($value as $v){
                     $this->header($name, (string) $v, false);
                 }
             }else{
                 $this->header($name, (string) $value);
             }
         }

         return $this;
     }

     public function get_header(string $name): ?array {
         return $this->headers[$this->normalize_header_name($name)] ?? null;
     }

     public function get_headers(): array {
         return $this->headers;
     }

     public function remove_header(string $name): static {
         unset($this->headers[$this->normalize_header_name($name)]);
        
         return $this;
     }

     public function cookie(
         string $name, 
         string $value,
         int $expires = 0,
         string $path = '/',
         string $domain = '',
         bool $secure = false,
         bool $http_only = true,
         string $same_site = 'Lax'
     ) : static {
         $this->cookies[] = compact(
             'name',
             'value',
             'expires',
             'path',
             'domain',
             'secure',
             'http_only',
             'same_site'
         );

         return $this;
     }

     public function delete_cookie(string $name, string $path = '/', string $domain = ''): static {
         return $this->cookie($name, '', time() - 3600, $path, $domain);
     }

     public function no_cache(): static {
         return $this
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
     }

     public function cache(int $seconds, bool $immutable = false): static {
         $value = "public, max-age={$seconds}";

         if($immutable){
             $value .= ', immutable';
         }

         return $this
            ->header('Cache-Control', $value)
            ->header('Expires', gmdate('D, d M Y H:i:s', time() + $seconds).' GMT');
     }

     public function is_sent(): bool {
         return $this->sent;
     }

     protected function send_headers(): void {
         if(headers_sent()){
             throw new RuntimeException('Headers already sent.');
         }

         http_response_code($this->status);

         foreach($this->headers as $name => $values){
             foreach($values as $index => $value){
                 header("{$name}: {$value}", $index === 0);
             }
         }

         foreach($this->cookies as $cookie){
             setcookie(
                 $cookie['name'],
                 $cookie['value'],
                 [
                     'expires' => $cookie['expires'],
                     'path' => $cookie['path'],
                     'domain' => $cookie['domain'],
                     'secure' => $cookie['secure'],
                     'httponly' => $cookie['http_only'],
                     'samesite' => $cookie['same_site'],
                 ]
             );
         }
     }

     protected function prepare_for_output(): void {
         while(ob_get_level() > 0){
             ob_end_flush();
         }
     }

     protected function normalize_header_name(string $name): string {
         return implode('-', array_map('ucfirst', explode('-', strtolower(trim($name)))));
     }

     final public function send(): void {
         if($this->sent){
             return;
         }

         $this->send_headers();
         $this->prepare_for_output();
         $this->send_content();
         $this->sent = true;
     }

     abstract protected function send_content(): void;
}
