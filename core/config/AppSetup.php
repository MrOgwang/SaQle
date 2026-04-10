<?php

namespace SaQle\Core\Config;

use SaQle\Core\Config\Config;

final class AppSetup {
     public function __construct(
         public string $environment = 'development',
         public string $base_path = '',
         public string $framework_path = '',
         public string $document_root = '',
         public array $providers = [],
         public $environment_loader = null,
         public ?string $config_dir = null,
         public array $cors = [],
     ){}

     public function get_framework_configs(){
         return Config::get_framework_configs($this->environment, $this->base_path, $this->framework_path, $this->document_root);
     }

     public function get_project_configs(){

         $config = !$this->config_dir ? [] : Config::load_configurations($this->config_dir);
         
         return Config::merge($config);
     }
}
