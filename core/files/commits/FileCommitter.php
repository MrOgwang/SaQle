<?php

namespace SaQle\Core\Files\Commits;

use SaQle\Orm\Entities\Field\Types\FileField;
use SaQle\Core\Files\Storage\{TempStorage, StorageFactory};
use RuntimeException;

class FileCommitter implements FileCommitInterface {

     protected FileField $field;
     
     protected array $committed = [];

     public function __construct(FileField $field){
         $this->field = $field;
     }

     public function commit(object $model, array $refs, array $row) : array {
         $storage = StorageFactory::make($this->field->get_storage());

         foreach($refs as $ref){
             $temp = TempStorage::resolve($ref);
             $final = $this->build_path($model, $row, $temp);
             $stream = fopen($temp, 'rb');

             if(!$storage->put($final, $stream)){
                 throw new RuntimeException("Storage write failed");
             }

             fclose($stream);

             unlink($temp);

             $this->committed[] = $final;
         }

         return $this->committed;
     }

     public function rollback(array $paths): void {
         $storage = StorageFactory::make($this->field->get_storage());

         foreach($paths as $path){
             $storage->delete($path);
         }
     }

     protected function build_path($model, $row, $temp){
         $upload_to = $this->field->get_upload_to();
         $filename  = basename($temp);

         if(is_callable($upload_to)){
            $upload_to = $upload_to($row, $model);
         }

         return trim($upload_to, '/').'/'.$filename;
     }
}