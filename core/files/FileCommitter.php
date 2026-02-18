<?php

namespace SaQle\Core\Files;

use SaQle\Core\Files\Storage\StorageFactory;
use SaQle\Orm\Entities\Field\Types\FileField;
use SaQle\Core\Files\Storage\TempStorage;
use SaQle\Core\Files\TempFileRef;
use RuntimeException;
use Closure;

final class FileCommitter {

     public static function commit(object $model, array $files, array $created_row): void {
         if(empty($files['references'])){
             return;
         }

         foreach($files['references'] as $field_name => $refs){
             $field = $model->table->fields[$field_name];

             if(!$field instanceof FileField){
                 continue;
             }

             $storage = StorageFactory::make($field->get_storage());

             $refs = is_array($refs) ? $refs : [$refs];
             $paths = [];

             foreach($refs as $ref){

                 if(!$ref instanceof TempFileRef){
                     throw new RuntimeException("Invalid temp file reference");
                 }

                 $temp_path = TempStorage::resolve($ref);
                 $final_path = self::build_final_path($model, $field, $created_row, $temp_path);

                 $stream = fopen($temp_path, 'rb');

                 $storage->put($final_path, $stream);
                 fclose($stream);

                 unlink($temp_path);

                 $paths[] = $final_path;
             }

             // Update DB field value
             self::update_model_field($model, $field_name, $paths, $created_row);
         }

         //Cleanup session directory
         TempStorage::cleanup_session($files['file_upload_session']);
     }

     private static function build_final_path(object $model, FileField $field, array $row, string $temp_path): string {
         $upload_to = $field->get_upload_to();
         $filename  = basename($temp_path);

         if($upload_to instanceof Closure){
             $upload_to = $upload_to($row, $model);
         }

         return trim($upload_to, '/').'/'.$filename;
     }

     private static function update_model_field(object $model, string $field, array $paths, array $row): void {
         $value = count($paths) === 1 ? $paths[0] : $paths;

         $model->query()->where('id', $row['id'])->update([$field => $value]);
     }
}
