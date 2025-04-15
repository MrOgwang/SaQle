<?php
namespace SaQle\Http\Request\Data\Sources\Managers\Types;

use SaQle\Http\Request\Data\Sources\From;

class ContextDataSourceManager extends DataSourceManager{

	 public function __construct(From $from, ...$kwargs){
	 	 parent::__construct($from, ...$kwargs);
	 }

	 public function get_value() : mixed {
	 	 $this->is_valid();
	 	 $refkey = $this->from->refkey ?? $this->name;
	 	 return $this->optional ? $this->request->context->get($refkey) : $this->request->context->get_or_fail($refkey);
	 }
}
?>