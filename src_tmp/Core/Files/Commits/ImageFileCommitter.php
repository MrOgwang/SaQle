<?php

namespace SaQle\Core\Files\Commits;

use SaQle\Orm\Entities\Field\Types\ImageField;
use SaQle\Core\Files\Storage\TempStorage;
use RuntimeException;

class ImageFileCommitter extends MediaFileCommitter {
     protected ImageField $field;

     public function __construct(ImageField $field){
         parent::__construct($field);
         $this->field = $field;
     }

     public function commit(object $model, array $refs, array $row): array {
         foreach($refs as $ref){
             $temp = TempStorage::resolve($ref);
             $processed = $this->process_image($temp);

             if(!$processed){
                 throw new RuntimeException("Image processing failed.");
             }
         }

         return parent::commit($model, $refs, $row);
     }

     protected function process_image(string $path): bool {
         $resize = $this->field->get_resize();
         $crop   = $this->field->get_crop();

         if(!$resize && !$crop){
            return true;
         }

         return ImageProcessor::process($path, $resize, $crop);
     }
}