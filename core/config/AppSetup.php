<?php

namespace SaQle\Core\Config;

use SaQle\Core\Config\ConfigDefaults;
use SaQle\Core\Assert\Assert;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

final class AppSetup{
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
         $config = [
            'environment'   => $this->environment,
            'base_path'     => $this->base_path,
            'document_root' => $this->document_root
         ];

         if(!$this->config_dir){
             return $config;
         }

         $iterator = new RecursiveIteratorIterator(
             new RecursiveDirectoryIterator($this->config_dir)
         );

         foreach($iterator as $file){
             if(!$file->isFile()) continue;

             if($file->getExtension() !== 'php') continue;

             $file_path = $file->getRealPath();

             $file_config = require $file_path;

             //make sure config is a key => value array
             Assert::isNonEmptyMap($file_config, 
             "The configuration file: {$file_path} is not correctly written. Return a key => value array from file");

             $config = array_replace($config, $file_config);
         }

         return ConfigDefaults::merge($config);
     }
}
