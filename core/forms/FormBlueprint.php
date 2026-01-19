<?php
declare(strict_types=1);

namespace SaQle\Core\Forms;

final class FormBlueprint{
     public function __construct(
         public readonly string $name,
         public readonly string $model_class,
         public readonly string $mode,
         public readonly bool   $auto_wire,
         public readonly array  $fields
     ) {}

     public function get(){
         return [
            'name' => $this->name,
            'model_class' => $this->model_class,
            'mode' => $this->mode,
            'auto_wire' => $this->auto_wire,
            'fields' => $this->fields
         ];
     }
}
