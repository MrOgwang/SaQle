<?php
namespace SaQle\Http\Request\Data\Sources\Managers\Types;

use SaQle\Http\Request\Data\Sources\Managers\Interfaces\IHttpDataSourceManager;
use SaQle\Http\Request\Data\Sources\From;
use SaQle\Http\Request\Request;

abstract class DataSourceManager implements IHttpDataSourceManager{

      protected Request $request;
	 protected From    $from;
	 protected string  $name;
	 protected string  $type;
	 protected mixed   $default;
	 protected bool    $optional;

	 public function __construct(From $from, ...$kwargs){
	 	 $this->request  = resolve('request');
	 	 $this->from     = $from;
	 	 $this->name     = $kwargs['name'];
	 	 $this->type     = $kwargs['type'];
	 	 $this->default  = $kwargs['default'];
	 	 $this->optional = $kwargs['optional'];
	 }

	 public function is_valid() : bool {
	 	 return true;
	 }
}
?>