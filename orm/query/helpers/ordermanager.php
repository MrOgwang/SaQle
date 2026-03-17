<?php
declare(strict_types = 1);

namespace SaQle\Orm\Query\Helpers;

use SaQle\Orm\Query\Order\{OrderBuilder, Order};
use RuntimeException;

trait OrderManager{
     /**
     * The order query builder
     * */
     public protected(set) OrderBuilder $obuilder {
         set(OrderBuilder $value){
             $this->obuilder = $value;
         }

         get => $this->obuilder;
     }

     public function __construct(){
         $this->obuilder = new OrderBuilder();
     }

     /**
     * Order the results returned by a select query.
     * @param array $fields     - the field names to order based on
     * @param string $direction - order ASC or DESC
     */
     public function order(array $fields, array | string $direction){
         $this->before_order();

         $direction = !is_array($direction) ? [ trim($direction) !== "" ? trim($direction) : "ASC"] : $direction;

         if(empty($direction)){
             $direction = ['ASC'];
         }

         $direction = array_map('strtoupper', $direction);

         foreach($direction as $dir){
             if(!in_array($dir, ["ASC", "DESC"])){
                 throw new RuntimeException("Invalid direction specified for order!");
             }
         }

         $field_count = count($fields);
         $dir_count   = count($direction);

         if($dir_count < $field_count){
             $last = end($direction);

             for($i = $dir_count; $i < $field_count; $i++) {
                $direction[] = $last;
             }
         }

         $this->obuilder->order = new Order(fields: $fields, direction: $direction);

         $this->after_order();

         return $this;
     }
     
     public function get_order_clause(){
         return $this->obuilder->construct_order_clause();
     }

     protected function before_order(){

     }

     protected function after_order(){
        
     }
}
