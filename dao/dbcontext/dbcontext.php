<?php
declare(strict_types = 1);
namespace SaQle\Dao\DbContext;

use SaQle\Dao\Model\Manager\IModelManager;
use SaQle\Dao\DbContext\Exceptions\ModelNotFoundException;
use function SaQle\Exceptions\{modelnotfoundexception};
use SaQle\Dao\Model\Model;
use SaQle\Dao\Model\Manager\ModelManager;
use SaQle\Services\Container\Cf;
use SaQle\Services\Container\ContainerService;

abstract class DbContext{
	private bool $is_dirty = false;
	public function __construct(private ?IModelManager $_model_manager = null){
		 $this->init();
	}
	private function init(){
		 if(is_null($this->_model_manager) || $this->is_dirty){
		 	 $this->_model_manager = Cf::create(ContainerService::class)->createContextModelManager($this::class);
		 }
		 $this->_model_manager->set_model_references($this->get_models());
		 /**
		  * Make the model manager self aware of the db context from which its operating
		  * */
		 $this->_model_manager->set_dbcontext_class($this::class);
	}
	static public abstract function get_models();
	public final function get_defined_models(){

	}
	public function __get($name){
		 return $this->get($name);
    }
    public function get($name){
    	 $this->is_dirty = true;
		 $this->init();
		 $this->_model_manager->register_joining_model($name);
		 return $this->_model_manager;
    }
    public static function get_dao_table_name(string $dao_class_name) : string | null{
    	 $contextclass = get_called_class();
    	 return array_flip($contextclass::get_models())[$dao_class_name] ?? null;
    }
}
?>