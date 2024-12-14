<?php
declare(strict_types = 1);

namespace SaQle\Dao\Model\Manager\Traits;

use SaQle\Dao\Group\Interfaces\IGroupManager;
use SaQle\Services\Container\Cf;

trait Group{
	 /**
     * The select manager handles selection
     * */
     private ?IGroupManager $gmanager = null;

     /**
      * Set the group manager
      * */
     protected function set_group_manager(){
         if(is_null($this->gmanager)){
             $this->gmanager = Cf::create(IGroupManager::class);
         }
     }

     /**
     * Get the group manager
     * @return IGroupManager
     */
     protected function get_group_manager() : IGroupManager{
         $this->set_group_manager();
         return $this->gmanager;
     }

     /**
     * Specify model fields to group the results by
     * @param array
     */
     public function group_by(array $fields){
         $this->get_group_manager()->set_groupby($fields);
         return $this;
     }

     protected function get_groupby_clause(){
         $gmanager = $this->get_group_manager();
         $gmanager->set_context_tracker($this->get_context_tracker());
         $fields = $gmanager->get_groupby(...$this->get_configurations());
         return $fields ? ' GROUP BY '.implode(", ", $fields) : "";
     }
}
?>