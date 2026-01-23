<?php
namespace SaQle\Http\Request\Data\Sources\Managers\Types;

use SaQle\Core\Support\BindFrom;

class PathDataSourceManager extends DataSourceManager{
	 public function __construct(BindFrom $from, ...$kwargs){
	 	 parent::__construct($from, ...$kwargs);
	 }

	 public function get_value() : mixed {
	 	 return $this->optional ? 
	 	 $this->request->params->get($this->from->key, $this->default) : 
	 	 $this->request->params->get_or_fail($this->from->key);
	 }
}
