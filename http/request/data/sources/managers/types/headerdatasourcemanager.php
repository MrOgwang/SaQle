<?php
namespace SaQle\Http\Request\Data\Sources\Managers\Types;

use SaQle\Http\Request\Data\Sources\From;

class HeaderDataSourceManager extends DataSourceManager{
	 public function __construct(From $from, ...$kwargs){
	 	 parent::__construct($from, ...$kwargs);
	 }

	 public function get_value() : mixed {
	 	 $this->is_valid();
	 	 return $this->optional ? $this->request->headers->get($this->name) : $this->request->headers->get_or_fail($this->name);
	 }
}
?>