<?php
declare(strict_types=1);

namespace SaQle\Core\Ui\Presenters;
 
use SaQle\Core\Registries\ModelRegistry;
use SaQle\Http\Request\Request;

final class Presenter {

     //form name as declared in the model
     private string $name;

     //render methods for fields
     private array $fields = [];

     public function __construct(string $name){
         $this->name = $name;
     }

     public function field(string $field_name, mixed $renderer){
         $this->fields[$field_name] = $renderer;
     }

     public function get_fields() : array {
         return $this->fields;
     }

     public function get_field(string $name) : mixed {
         return $this->fields[$name] ?? null;
     }

}