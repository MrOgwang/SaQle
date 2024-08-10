<?php
declare(strict_types=1);

namespace SaQle\Dao\Model\Interfaces;

use SaQle\Core\Collection\Base\TypedCollection;

abstract class ModelCollection extends TypedCollection{
    public function __construct(array $elements = []){
         parent::__construct($elements);
    }

    abstract protected function type(): string;

    public function add(mixed $element): void{
        parent::add($element);
    }

    public function save(...$extra){
         $saved_elements = [];
         foreach($this->elements as $el){
             $saved_elements[] = $el->save(...$extra);
         }
         $class_name = $this::class;
         return new $class_name($saved_elements);
    }

    public function get_field_value($name){
         $field_values = [];
         foreach($this->elements as $el){
             $field_values[] = $el->get_field_value($name);
         }
         return $field_values;
    }
}
?>