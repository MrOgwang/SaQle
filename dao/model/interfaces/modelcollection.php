<?php
declare(strict_types=1);

namespace SaQle\Dao\Model\Interfaces;

use SaQle\Core\Collection\Base\TypedCollection;
use SaQle\Dao\Model\Interfaces\IModel;

abstract class ModelCollection extends TypedCollection implements IModel{
    public function __construct(array $elements = []){
         parent::__construct($elements);
    }

    abstract protected function type(): string;

    public function add(mixed $element): void{
        parent::add($element);
    }

    public function save(){
         $saved_elements = [];
         foreach($this->elements as $el){
             $saved_elements[] = $el->save();
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