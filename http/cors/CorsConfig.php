<?php

namespace SaQle\Http\Cors;

class CorsConfig {
     private array $origins = [];
     private array $methods = [];
     private array $headers = [];
     private array $required_headers = [];
     private bool  $credentials = false;

     public function __construct(array $config){
        
     }

     //boot time methods
     public function origins(array $origins): self{
         $this->origins = array_values(array_unique($origins));
         return $this;
     }

     public function methods(array $methods): self {
         $this->methods = array_values(array_unique(array_map('strtoupper', $methods)));
         return $this;
     }

     public function headers(array $headers): self {
         $this->headers = array_values(array_unique($headers));
         return $this;
     }

     public function required_headers(array $headers): self{
         $this->required_headers = array_values(array_unique($headers));
         return $this;
     }

     public function credentials(bool $allow = true): self{
         $this->credentials = $allow;
         return $this;
     }

     //runtime access
     public function get_origins(): array{
         return $this->origins;
     }

     public function get_methods(): array{
         return $this->methods;
     }

     public function get_headers(): array {
         return $this->headers;
     }

     public function get_required_headers(): array{
         return $this->required_headers;
     }

     public function allows_credentials(): bool{
         return $this->credentials;
     }

     //convenient helpers
     public function allows_origin(string $origin): bool{
         return in_array('*', $this->origins, true) || in_array($origin, $this->origins, true);
     }

     public function allows_method(string $method): bool{
         return in_array(strtoupper($method), $this->methods, true);
     }
}
