<?php

namespace SaQle\Core\Registries;

use InvalidArgumentException;

final class ModelRegistry {
     private static ?array $models = null;

     public static function all(): array {
         if(self::$models === null) {
             self::$models = require path_join([config('base_path'), config('class_mappings_dir'), 'models.php']);
         }

         return self::$models;
     }

     public static function get_long_model_name(string $model_class) : ?string {
         $all_models = self::all();
         $flipped = array_flip($all_models);

         return $flipped[$model_class] ?? null;
     }

     public static function get_model_name(string $model_class) : ?string {
         $long_model_name = self::get_long_model_name($model_class);
         if(!$long_model_name){
             return null;
         }

         $name_parts = explode(".", $long_model_name);
         
         return end($name_parts);
     }
}
