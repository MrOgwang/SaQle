<?php
declare(strict_types = 1);

namespace SaQle\Orm\Query\Helpers;

use SaQle\Orm\Query\Order\{OrderBuilder, Order};

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
     public function order(array $fields, string $direction = "ASC"){
         $this->before_order();

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
