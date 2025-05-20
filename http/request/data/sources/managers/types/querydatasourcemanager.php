<?php
namespace SaQle\Http\Request\Data\Sources\Managers\Types;

use SaQle\Http\Request\Data\Sources\From;

class QueryDataSourceManager extends DataSourceManager{

	 public function __construct(From $from, ...$kwargs){
	 	 parent::__construct($from, ...$kwargs);
	 }

	 public function get_value() : mixed {
	 	 $this->is_valid();
	 	 return $this->optional ? $this->request->route->queries->get($this->name) : $this->request->route->queries->get_or_fail($this->name);
	 }
}
