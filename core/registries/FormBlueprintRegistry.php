<?php
declare(strict_types=1);

namespace SaQle\Core\Registries;

use RuntimeException;

final class FormBlueprintRegistry {
     /** @var array<string, array> Loaded blueprints in memory */
     private static array $cache = [];

     /**
     * Get a blueprint by form name.
     *
     * @param string $form_name
     * @return array
     */
     public static function get(string $form_name): array {
         if(array_key_exists($form_name, self::$cache)){
             return self::$cache[$form_name];
         }

         $path = path_join([config('base_path'), config('forms_cache_dir'), $form_name.".php"]);

         if(!file_exists($path)){
             throw new RuntimeException("The form {$form_name} has not been compiled!");
         }

         $blueprint = require $path;

         if(!is_array($blueprint)) {
             throw new RuntimeException('Form blueprint cache file must return an array.');
         }

         self::$cache[$form_name] = $blueprint;

         return $blueprint;
     }
}
