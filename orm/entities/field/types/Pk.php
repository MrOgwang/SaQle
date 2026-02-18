<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class Pk implements IField {

     protected string $strategy;

     public function __construct(?string $strategy = null){
        
         $strategy = $strategy ?? config('primary_key_type');
         $this->strategy = strtoupper($strategy);
     }

     public function resolve(...$kwargs): Field {
         return match ($this->strategy){
             'GUID' => $this->build_uuid(...$kwargs),
             'AUTO' => $this->build_integer(...$kwargs),
         };
     }

     protected function build_integer(...$kwargs): IntegerField {
         return new IntegerField(...array_merge($kwargs, ['primary' => true, 'auto' => true, 'unsigned' => true, 'required' => true]));
     }

     protected function build_uuid(...$kwargs): UuidField {
         return new UuidField(...array_merge($kwargs, ['primary' => true, 'required' => true]));
     }
}

