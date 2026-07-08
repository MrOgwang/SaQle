<?php
declare(strict_types = 1);

namespace SaQle\Orm\Query\Helpers;

use SaQle\Orm\Query\Group\GroupBuilder;

trait GroupManager{
	 /**
     * The group query builder
     * */
     public protected(set) GroupBuilder $gbuilder {
         set(GroupBuilder $value){
             $this->gbuilder = $value;
         }

         get => $this->gbuilder;
     }

     public function __construct(){
         $this->gbuilder = new GroupBuilder();
     }

     /**
     * Specify model fields to group the results by
     * @param array
     */
     public function group_by(array $fields){
         $this->before_group();

         $this->gbuilder->fields = $fields;

         $this->after_group();

         return $this;
     }

     public function get_groupby_clause(){
         $fields = $this->gbuilder->get_groupby($this->query_reference_map, ...$this->configurations);
         return $fields ? ' GROUP BY '.implode(", ", $fields) : "";
     }

     protected function before_group(){

     }

     protected function after_group(){
        
     }
}
