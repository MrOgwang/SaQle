<?php
declare(strict_types = 1);

namespace SaQle\Dao\Model\Manager\Traits;

use SaQle\Dao\Select\Interfaces\ISelectManager;
use SaQle\Services\Container\Cf;
use Closure;

trait Select{
	 /**
     * The select manager handles selection
     * */
     private ?ISelectManager $smanager = null;

     /**
      * The callback will be applied on the selected array
      * */
     private ?Closure $callback = null;

     /**
      * Set the select manager
      * */
     protected function set_select_manager(){
         if(is_null($this->smanager)){
             $this->smanager = Cf::create(ISelectManager::class);
         }
     }

     /**
     * Get the select manager
     * @return ISelectManager
     */
     protected function get_select_manager() : ISelectManager{
         $this->set_select_manager();
         return $this->smanager;
     }

     /**
     * Specify model fields to return in a select operation. Fields can be qualified with . operator. Example users.first_name;
     * @param array
     * @throw DatabaseNotFoundException
     * @throw ModelNotFoundException
     * @throw FieldNotFoundException
     */
     public function select(?array $fields = null, ?Closure $callback = null){
         $this->callback = $callback;
         $this->get_select_manager()->set_selected($fields);
         return $this;
     }

     protected function get_selected(){
         $smanager = $this->get_select_manager();
         $smanager->set_context_tracker($this->get_context_tracker());
         $selected = $smanager->get_selected(...$this->get_configurations());
         if($this->callback){
             $fn = $this->callback;
             return $fn($selected);
         }
         return implode(", ", $selected);
     }

     protected function get_selected_fields(){
         $smanager = $this->get_select_manager();
         $smanager->set_context_tracker($this->get_context_tracker());
         return $smanager->get_selected(...$this->get_configurations());
     }
}
?>