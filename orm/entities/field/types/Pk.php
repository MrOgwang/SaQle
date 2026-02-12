<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Field\Types\Base\Field;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class Pk implements IField {

     protected string $strategy;

     public function __construct(?string $strategy = null){
         $strategy = $strategy ?? config('primary_key_type');
         $this->strategy = strtoupper($strategy);
     }

     public function resolve(): Field {
         return match ($this->strategy){
             'GUID' => $this->build_uuid(),
             'AUTO' => $this->build_integer(),
         };
     }

     protected function build_integer(): IntegerField {
         return new IntegerField(
             primary: true,
             auto: true,
             unsigned: true,
             required: true
         );
     }

     protected function build_uuid(): UuidField {
         return new UuidField(
             primary: true,
             required: true
         );
     }
}

