<?php
declare(strict_types=1);

namespace SaQle\Core\Registries;

final class FormBlueprintRegistry {
     /** @var array<string, array> Loaded blueprints in memory */
     private static array $cache = [];

     /**
     * Get a blueprint by form name.
     *
     * @param string $form_name
     * @return array
     */
     public static function get(string $form_name): array | null {
         if(array_key_exists($form_name, self::$cache)){
             return self::$cache[$form_name];
         }

         $path = path_join([config('base_path'), config('forms_cache_dir'), $form_name.".php"]);

         if(!file_exists($path)){
             return null;
         }

         $blueprint = require $path;

         /**
          * TO DO:
          * 
          * Other than just checking if blueprint is an array,
          * you may want to do proper blueprint validation here!
          * */
         if(!is_array($blueprint)){
             return null;
         }

         self::$cache[$form_name] = $blueprint;

         return $blueprint;
     }
}
