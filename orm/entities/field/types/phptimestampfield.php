<?php
namespace SaQle\Orm\Entities\Field\Types;

class PhpTimestampField extends BigIntegerField{
	 public function __construct(...$kwargs){
		 $kwargs['value'] = time();
		 parent::__construct(...$kwargs);
	 }
}
