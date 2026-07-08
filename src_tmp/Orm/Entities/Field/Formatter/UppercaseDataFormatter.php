<?php
namespace SaQle\Orm\Entities\Field\Formatter;

class UppercaseDataFormatter extends IDataFormatter{
	 public function format($value){
	 	 $value = $this->formatter->format($value);
	 	 return strtoupper($value);
	 }
}
