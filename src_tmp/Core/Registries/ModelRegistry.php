<?php

namespace SaQle\Core\Registries;

use InvalidArgumentException;
use SaQle\Core\Support\{
     AttributeResolver,
     SchemaIndex
};

final class ModelRegistry {

     private static ?array $models = null;

     public static function all(): array {
         if(self::$models === null) {
             $file = path_join([config('base_path'), config('class_mappings_dir'), 'models.php']);
             self::$models = file_exists($file) ? require $file : [];
         }

         return self::$models;
     }

     public static function get_module_models(string $module_class) : array {

         $module = new $module_class();

         $namespace = strtolower(explode('\\', $module_class)[0]).".".$module->manifest()->name;

         $all_models = self::all();

         $module_models = [];

         foreach($all_models as $name => $class){
             if(str_starts_with($name, $namespace)){

                 /**
                  * The number 10000 means nothing more than to make sure models
                  * without explicit indexes appear last in the list.
                  * */
                 $index = count($module_models) + 10000;

                 $index_attributes = (new AttributeResolver())->get_class_attributes($class, SchemaIndex::class, true);

                 if($index_attributes){
                     $index = $index_attributes[0]->index;
                 }

                 $module_models[] = (Object)[
                     'class' => $class,
                     'index' => $index
                 ];
             }
         }

         usort($module_models, function ($a, $b){
             return $a->index <=> $b->index;
         });

         return array_map(fn($model) => $model->class, $module_models);
     }

     public static function get_long_model_name(string $model_class) : ?string {
         $all_models = self::all();
         $flipped = array_flip($all_models);

         return $flipped[$model_class] ?? null;
     }

     public static function get_model_class(string $model_key) : ?string {
         $all_models = self::all();
         return $all_models[$model_key] ?? null;
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
