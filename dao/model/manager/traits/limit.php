<?php
declare(strict_types = 1);

namespace SaQle\Dao\Model\Manager\Traits;

use SaQle\Dao\Limit\Interfaces\ILimitManager;
use SaQle\Services\Container\Cf;

trait Limit{
	 /**
     * The limit manager handles limits
     * */
     private ?ILimitManager $lmanager = null;

     /**
      * Set the limit manager
      * */
     protected function set_limit_manager(){
         if(is_null($this->lmanager)){
             $this->lmanager = Cf::create(ILimitManager::class);
         }
     }

     /**
     * Get the limit manager
     * @return ILimitManager
     */
     protected function get_limit_manager() : ILimitManager{
         $this->set_limit_manager();
         return $this->lmanager;
     }

     /**
     * Limit the number of rows returned by a select query.
     * @param int $page - the page to fetch
     * @param int records - the number of records to fetch.
     */
     public function limit(int $page = 1, int $records = 10){
         $this->get_limit_manager()->set_limit(page: $page, records: $records);
         return $this;
     }

     protected function get_limit_clause(){
         return $this->get_limit_manager()->construct_limit_clause();
     }

     protected function get_limit_page(){
         $limit = $this->get_limit_manager()->get_limit();
         return $limit ? $limit->get_page() : 0;
     }

     protected function get_limit_records(){
         $limit = $this->get_limit_manager()->get_limit();
         return $limit ? $limit->get_records() : 0;     
     }
}
?>