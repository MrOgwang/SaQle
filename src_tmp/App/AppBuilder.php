<?php

namespace SaQle\App;

use SaQle\Core\Support\Environment;

abstract class AppBuilder {

     protected AppSetup $setup;

     public function __construct(string $base_path){

         $this->setup = new AppSetup();

         $this->setup->base_path = realpath($base_path);

         $this->setup->document_root = $this->setup->base_path.'/public';

         $this->setup->config_dir = $this->setup->base_path.'/config';
     }

     public function public_path(string $path): self {
         
         $this->setup->document_root = $path;

         return $this;
     }

     public function config_path(string $path) : self {
         
         $this->setup->config_dir = $path;

         return $this;
     }

     public function environment(Environment $environment): self {
         
         $this->setup->environment = $environment;

         return $this;
     }

     public function providers(string ...$providers): self {
         
         $this->setup->providers = $providers;

         return $this;
     }
    
     public function build(): App {
         
         $this->setup->initialize();

         return new App($this->setup);
     }
}