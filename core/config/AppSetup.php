<?php

namespace SaQle\Core\Config;

use SaQle\Core\Config\Config;

final class AppSetup {
     public function __construct(
         public string $environment = 'development',
         public string $base_path = '',
         public string $document_root = '',
         public array $providers = [],
         public array $middlewares = [],
         public $environment_loader = null,
         public ?string $config_dir = null,
         public array $cors = [],
     ){}

     public function get_configurations(){
         $config = Config::get_framework_configs($this->environment, $this->base_path, $this->document_root);
         
         if(!$this->config_dir){
             return $config;
         }

         $config = array_merge($config, Config::load_configurations($this->config_dir));

         return Config::merge($config);
     }
}
