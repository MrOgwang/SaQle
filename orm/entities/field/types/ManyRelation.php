<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Field\Types\Base\Field;

class ManyRelation implements IField {

     protected array $kwargs = [];

     public function __construct(string $model, ?string $local_key = null, ?string $foreign_key = null){
         $this->kwargs['related_model'] = $model;
         $this->kwargs['local_key'] = $local_key;
         $this->kwargs['foreign_key'] = $foreign_key;
     }

     public function through(string $model){
         $this->kwargs['through'] = $model;
         return $this;
     }

     public function resolve() : Field {
         if(isset($this->kwargs['through'])){
             return new ManyToMany(...$this->kwargs);
         }

         return new OneToMany(...$this->kwargs);
     }

     public function foreign_key(string $foreign_key){
         $this->kwargs['foreign_key'] = $foreign_key;
         return $this;
     }

     public function local_key(string $local_key){
         $this->kwargs['local_key'] = $local_key;
         return $this;
     }
}

