<?php

namespace SaQle\Core\Config;

use SaQle\Core\Config\Config;
use SaQle\Core\Support\Environment;
use Dotenv\Dotenv;
use Closure;

final class AppSetup {

     public ?Closure $environment_loader = null;
     public ?string $framework_path = null;

     public function __construct(
         public string $base_path,
         public string $document_root,
         public string $config_dir,
         public Environment $environment = Environment::DEVELOPMENT,
         public array $providers = [],
         public array $cors = []
     ){
         $base_path = $this->base_path;

         $this->environment_loader = function(Environment $environment) use ($base_path){
             $env_dir = $base_path.'/env/'.$environment->value;
             if(file_exists($env_dir.'/.env')){
                 Dotenv::createImmutable($env_dir)->load();
             }
         };
         
         $this->framework_path = realpath(__DIR__.'/../../../');
     }

     public function get_framework_configs(){
         return Config::get_framework_configs(
             $this->environment->value, 
             $this->base_path, 
             $this->framework_path, 
             $this->document_root
         );
     }

     public function get_project_configs(){

         $config = !$this->config_dir ? [] : Config::load_configurations($this->config_dir);
         
         return Config::merge($config);
     }
}
