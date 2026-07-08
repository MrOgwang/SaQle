<?php
namespace SaQle\Http\Request\Data\Sources\Managers\Types;

use SaQle\Core\Support\BindFrom;

class HeaderDataSourceManager extends DataSourceManager{
	 public function __construct(BindFrom $from, ...$kwargs){
	 	 parent::__construct($from, ...$kwargs);
	 }

	 public function get_value() : mixed {
	 	 return $this->optional ? 
	 	 $this->request->headers->get($this->from->key, $this->default) : 
	 	 $this->request->headers->get_or_fail($this->from->key);
	 }
}
