<?php

namespace SaQle\App;

final class CorsBuilder {

     private array $config = [
         'required_headers' => [],
         'credentials' => false,
         'origins' => []
     ];

     public function allow_origins(string ...$origins): self {
         $this->config['origins'] = $origins;

         return $this;
     }

     public function allow_credentials(bool $enabled = true): self {
         $this->config['credentials'] = $enabled;

         return $this;
     }

     public function required_headers(string ...$headers): self {
         $this->config['required_headers'] = $headers;

         return $this;
     }

     public function allow_all(): self {
         return $this->allow_origins('*');
     }

     public function to_array(): array {
         return $this->config;
     }
}