<?php
declare(strict_types = 1);

namespace SaQle\Dao\Model\Manager\Traits;

use SaQle\Dao\Order\Interfaces\IOrderManager;
use SaQle\Services\Container\Cf;

trait Order{
	 /**
     * The order manager handles data ordering
     * */
     private ?IOrderManager $omanager = null;
 
     /**
      * Set the limit manager
      * */
     protected function set_order_manager(){
         if(is_null($this->omanager)){
             $this->omanager = Cf::create(IOrderManager::class);
         }
     }

     /**
     * Get the order manager
     * @return IOrderManager
     */
     protected function get_order_manager() : IOrderManager{
         $this->set_order_manager();
         return $this->omanager;
     }

     /**
     * Order the results returned by a select query.
     * @param array $fields     - the field names to order based on
     * @param string $direction - order ASC or DESC
     */
     public function order(array $fields, string $direction = "ASC"){
         $this->get_order_manager()->set_order(fields: $fields, direction: $direction);
         return $this;
     }
     
     protected function get_order_clause(){
         return $this->get_order_manager()->construct_order_clause();
     }
}
?>