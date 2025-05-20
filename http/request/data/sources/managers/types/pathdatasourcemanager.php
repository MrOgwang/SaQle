<?php
namespace SaQle\Http\Request\Data\Sources\Managers\Types;

use SaQle\Http\Request\Data\Sources\From;

class PathDataSourceManager extends DataSourceManager{
	 public function __construct(From $from, ...$kwargs){
	 	 parent::__construct($from, ...$kwargs);
	 }

	 public function get_value() : mixed {
	 	 $this->is_valid();
	 	 $refkey = $this->from->refkey ?? $this->name;
	 	 return $this->optional ? $this->request->route->params->get($refkey) : $this->request->route->params->get_or_fail($refkey);
	 }
}
