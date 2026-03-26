<?php

namespace SaQle\Core\Config;

use SaQle\Core\Assert\Assert;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

final class Config {

     public static function merge(array $config): array {
         return array_replace_recursive(self::defaults(), $config);
     }

     public static function load_configurations(string $config_dir) : array {
         if(!is_dir($config_dir))
             return [];
         
         $config = [];

         $iterator = new RecursiveIteratorIterator(
             new RecursiveDirectoryIterator($config_dir)
         );

         foreach($iterator as $file){
             if(!$file->isFile() || $file->getExtension() !== 'php'){
                 continue;
             }

             $file_path = $file->getRealPath();
             $file_config = require $file_path;

             //Ensure config is a key => value array
             Assert::isNonEmptyMap(
                 $file_config,
                "The configuration file: {$file_path} must return a key => value array"
             );

             //Get file name without extension as namespace
             $namespace = pathinfo($file->getFilename(), PATHINFO_FILENAME);

             //Flatten keys with namespace
             foreach($file_config as $key => $value){
                 $config["{$namespace}.{$key}"] = $value;
             }
         }

         return $config;
     }

     private static function defaults(): array {
         $config_dir = __DIR__."/defaults";

         return self::load_configurations($config_dir);
     }

     public static function get_framework_configs($environment, $base_path, $framework_path, $document_root){
         return [
             //the environment
             'environment'   => $environment,

             //project root directory
             'base_path'     => $base_path,

             //framework path
             'framework_path' => $framework_path,

             //project document root
             'document_root' => $document_root,

             /**
             * The directory in which class mappings will be cached. 
             * This is used by the framework and cannot be customized
             * at the moment
             * */
             'class_mappings_dir' => '/storage/framework/build/cache/mappings/',

             /**
             * The directory in which compiled views will be cached. 
             * This is used by the framework and cannot be customized
             * at the moment
             * */
             'templates_cache_dir' => '/storage/framework/build/cache/templates/',

             /**
             * The directory in which compiled assets(css/js/media) will be cached. 
             * This is used by the framework and cannot be customized
             * at the moment
             * */
             'assets_cache_dir' => '/storage/framework/build/cache/assets/',

             /**
             * The directory in which compiled forms will be cached. 
             * This is used by the framework and cannot be customized
             * at the moment
             * */
             'forms_cache_dir' => '/storage/framework/build/cache/forms/',

             'saqle_components_dirs' => [realpath(__DIR__.'/../../components')],

             'saqle_routes_dirs' => [realpath(__DIR__.'/../../routes')],

             'static_assets_route' => '/private-asset'
         ];
     }
}
