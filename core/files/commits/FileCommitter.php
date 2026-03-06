<?php

namespace SaQle\Core\Files\Commits;

use SaQle\Orm\Entities\Field\Types\FileField;
use SaQle\Core\Files\Storage\{TempStorage, StorageFactory};
use RuntimeException;
use Throwable;

class FileCommitter implements FileCommitInterface {

     protected FileField $field;
     
     protected array $committed = [];

     public function __construct(FileField $field){
         $this->field = $field;
     }

     public function commit(object $model, array $refs, mixed $row) : array {

         $storage_name = $this->field->get_storage();
         $storage = StorageFactory::make($storage_name);

         foreach($refs as $n => $ref){
             $temp = TempStorage::resolve($ref);
             $final = $this->build_path($row, basename($temp), $ref->file_name, $n);

             $stream = fopen($temp, 'rb');

             try{
                 $storage->put($final, $stream);
             }catch(Throwable $e){
                 //log the exception here!
                 throw new RuntimeException("Storage write failed");
             }finally{
                 fclose($stream);
             }

             unlink($temp);

             $this->committed[] = [
                 'storage' => $storage_name,
                 'original_name' => $ref->file_name,
                 'name' => basename($final),
                 'path' => $final,
                 'size' => $ref->size,
                 'mime' => $ref->mime
             ];
         }

         return $this->committed;
     }

     public function rollback(array $commits): void {
         $storage = StorageFactory::make($this->field->get_storage());

         foreach($commits as $commit){
             $storage->delete($commit['relative_path']);
         }
     }

     protected function build_path($row, $file_name, $original_file_name, $file_index){
         $upload_to = $this->field->get_upload_to() ?? "";
         $rename_to = $this->field->get_rename_to();

         //if rename to was provided, rename the file.
         if($rename_to && is_callable($rename_to)){
             $file_name = $rename_to($row, $original_file_name, $file_index);
         }

         //get upload to path
         if(is_callable($upload_to)){
             $upload_to = $upload_to($row);
         }

         return path_join([$upload_to, $file_name]);
     }
}