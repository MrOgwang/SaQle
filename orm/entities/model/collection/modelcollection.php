<?php
declare(strict_types=1);

namespace SaQle\Orm\Entities\Model\Collection;

use SaQle\Core\Collection\Base\TypedCollection;
use SaQle\Orm\Entities\Model\Interfaces\IModel;

class ModelCollection extends TypedCollection implements IModel {
     private string $type;
     public function __construct(string $type, array $elements = []){
         $this->type = $type;
         parent::__construct($elements);
     }

     protected function type(): string{
        return $this->type;
     }

     public static function from_objects(string $type, array $objects = []){
         //print_r($objects);

         $models = [];
         foreach($objects as $object){
             $models[] = new $type(...get_object_vars($object));
         }

         return new static($type, $models);
     }

     public static function from_arrays(string $type, array $elements = []){

     }

     public static function from_models(string $type, array $elements = []){

     }
}
