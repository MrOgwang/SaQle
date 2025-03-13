<?php
declare(strict_types = 1);

namespace SaQle\Orm\Database;

use SaQle\Orm\Entities\Model\Manager\IModelManager;
use SaQle\Orm\Database\Exceptions\ModelNotFoundException;
use function SaQle\Exceptions\{modelnotfoundexception};
use SaQle\Orm\Entities\Model\Model;
use SaQle\Orm\Entities\Model\Manager\ModelManager;
use SaQle\Services\Container\Cf;
use SaQle\Services\Container\ContainerService;

abstract class DbContext{
	public function __construct(private ?IModelManager $_model_manager = null){}
	private function init($name){
		 $this->_model_manager = Cf::create(ContainerService::class)->createContextModelManager($this::class);
		 $this->_model_manager->set_dbcontext_class($this::class);
		 $this->_model_manager->register_joining_model($name);
		 return $this->_model_manager;
	}
	static public abstract function get_models();
	public function __get($name){
		 return $this->get($name);
    }
    public function get($name){
		 return $this->init($name);
    }
    public static function get_dao_table_name(string $dao_class_name) : string | null{
    	 $contextclass = get_called_class();
    	 return array_flip($contextclass::get_models())[$dao_class_name] ?? null;
    }
}
?>