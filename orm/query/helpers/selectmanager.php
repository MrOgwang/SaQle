<?php
declare(strict_types = 1);

namespace SaQle\Orm\Query\Helpers;

use SaQle\Orm\Query\Select\SelectBuilder;
use Closure;

trait SelectManager{
     /**
     * The select query builder
     * */
     public protected(set) SelectBuilder $sbuilder {
         set(SelectBuilder $value){
             $this->sbuilder = $value;
         }

         get => $this->sbuilder;
     }

     /**
      * The callback will be applied on the selected array
      * */
     private ?Closure $callback = null;

     public function __construct(){
         $this->sbuilder = new SelectBuilder();
     }

     /**
     * Specify model fields to return in a select operation. Fields can be qualified with . operator. Example users.first_name;
     * @param array
     */
     public function select(?array $fields = null, ?Closure $callback = null){
         $this->before_select($fields, $callback);

         $this->callback = $callback;
         $this->sbuilder->selected = $fields;

         $this->after_select($fields, $callback);

         return $this;
     }

     public function get_selected(){
         $selected = $this->sbuilder->get_selected($this->query_reference_map, ...$this->configurations);
         if($this->callback){
             $fn = $this->callback;
             return $fn($selected);
         }
         return implode(", ", $selected);
     }

     public function get_selected_fields(){
         return $this->sbuilder->get_selected($this->query_reference_map, ...$this->configurations);
     }

     protected function before_select(?array $fields = null, ?Closure $callback = null){

     }

     protected function after_select(?array $fields = null, ?Closure $callback = null){
        
     }
}
