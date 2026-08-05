<?php

namespace SaQle\Core\Registries;

use InvalidArgumentException;
use SaQle\Orm\Entities\Model\TempId;

final class TableRegistry {

     private static ?array $tables = null;

     public static function all(): array {
         if(self::$tables === null) {
             self::$tables = require path_join([config('base_path'), config('class_mappings_dir'), 'tables.php']);
         }

         return self::$tables;
     }

     public static function get_table_model(string $table) : ?string {

         if($table === "model_temp_ids"){
             return TempId::class;
         }
         
         return self::all()[$table] ?? null;

     }

     public static function get_model_table(string $model_class) : ?string {

         if($model_class === TempId::class){
             return "model_temp_ids";
         }
         
         $all_tables = self::all();

         $flipped = array_flip($all_tables);

         return $flipped[$model_class] ?? null;

     }
}
