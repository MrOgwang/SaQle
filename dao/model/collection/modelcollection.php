<?php
declare(strict_types=1);

namespace SaQle\Dao\Model\Collection;

use SaQle\Core\Collection\Base\TypedCollection;
use SaQle\Dao\Model\Interfaces\IModel;

class ModelCollection extends TypedCollection implements IModel{
    private string $type;
    public function __construct(string $type, array $elements = []){
         $this->type = $type;
         parent::__construct($elements);
    }

    protected function type(): string{
        return $this->type;
    }

    public function save(){
         $saved_elements = [];
         foreach($this->elements as $el){
             $saved_elements[] = $el->save();
         }
         $class_name = $this::class;
         return new $class_name(type: $this->type(), elements: $saved_elements);
    }

    public function __get($name){
         $field_values = [];
         foreach($this->elements as $el){
             $field_values[] = $el->$name;
         }
         return $field_values;
    }
}
?>