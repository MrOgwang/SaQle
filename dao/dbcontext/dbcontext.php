<?php
declare(strict_types = 1);
namespace SaQle\Dao\DbContext;

use SaQle\Dao\Model\Manager\IModelManager;
use SaQle\Dao\DbContext\Exceptions\ModelNotFoundException;
use function SaQle\Exceptions\{modelnotfoundexception};
use SaQle\Dao\Model\Model;
use SaQle\Dao\Model\Manager\ModelManager;
use SaQle\Services\Container\Cf;

abstract class DbContext{
	private bool $is_dirty = false;
	public function __construct(private ?IModelManager $_model_manager = null){
		 $this->init();
	}
	private function init(){
		 if(is_null($this->_model_manager) || $this->is_dirty){
	         $this->_model_manager = Cf::create(ModelManager::class);
		 }
		 $this->_model_manager->set_model_references($this->get_models());
		 /**
		  * Make the model manager self aware of the db context from which its operating
		  * */
		 $this->_model_manager->set_dbcontext_class($this::class);
	}
	static public abstract function get_models();
	public function __get($name){
		 return $this->get($name);
    }
    public function get($name){
    	 $this->is_dirty = true;
		 $this->init();
		 $model_references = $this->_model_manager->get_model_references();
		 modelnotfoundexception($name, $model_references, $this::class);
		 $dao_class    = $model_references[$name];
		 $dao_instance = new $dao_class();
		 $dao_instance->set_request($this->_model_manager->get_request());
		 $model        = new Model($dao_instance);
		 /*register model with the model manager*/
		 $this->_model_manager->add_model($name, $model);
		 /*register model info with the context tracker*/
		 $this->_model_manager->register_to_context_tracker(
		 	 table_name:    $name,
		 	 table_aliase:  "",
		 	 database_name: $this->_model_manager->get_context_options()->get_name(),
		 	 field_list:    $model->get_field_names()
		 );
		 return $this->_model_manager;
    }
    public function get_dao_table_name(string $dao_class_name) : string | null{
    	 $models = $this->get_models();
    	 foreach($models as $tn => $dn){
    	 	if($dn === $dao_class_name){
    	 		return $tn;
    	 	}
    	 }
    	 return null;
    }
}
?>