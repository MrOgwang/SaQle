<?php
namespace SaQle\Dao\Field\Types;

class PhpTimestampField extends BigIntegerField{
	 public function __construct(...$kwargs){
		 $kwargs['content'] = time();
		 parent::__construct(...$kwargs);
	 }
}
?>