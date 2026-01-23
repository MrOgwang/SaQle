<?php
namespace SaQle\Http\Request\Data\Sources\Managers\Types;

use SaQle\Http\Request\Data\Sources\Managers\Interfaces\IHttpDataSourceManager;
use SaQle\Core\Support\BindFrom;
use SaQle\Http\Request\Request;
use ReflectionType;

abstract class DataSourceManager implements IHttpDataSourceManager{
	 //request contains data
     protected Request $request;

     //which areas of the request to find data
	 protected BindFrom $from;

	 //the name of the parameter
	 protected string $name;

	 //the parameter type
	 protected ?ReflectionType $type;

	 //the default value of parameter
	 protected mixed $default;

	 //whether the parameter is optional
	 protected bool $optional;

	 public function __construct(BindFrom $from, ...$kwargs){
	 	 $this->request  = resolve('request');
	 	 $this->from     = $from;
	 	 $this->name     = $kwargs['name'];
	 	 $this->type     = $kwargs['type'] ?? null;
	 	 $this->default  = $kwargs['default'];
	 	 $this->optional = $kwargs['optional'];
	 }

	 public function is_valid() : bool {
	 	 return true;
	 }
}
