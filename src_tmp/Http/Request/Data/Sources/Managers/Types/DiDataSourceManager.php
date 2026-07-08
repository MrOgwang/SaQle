<?php
namespace SaQle\Http\Request\Data\Sources\Managers\Types;

use SaQle\Core\Support\BindFrom;
use SaQle\Http\Request\Execution\TypeInspector;

class DiDataSourceManager extends DataSourceManager{

	 public function __construct(BindFrom $from, ...$kwargs){
	 	 parent::__construct($from, ...$kwargs);
	 }

	 public function get_value() : mixed {
	 	 $class_name = TypeInspector::get_class_name($this->type);
	 	 return resolve($class_name);
	 }
}
