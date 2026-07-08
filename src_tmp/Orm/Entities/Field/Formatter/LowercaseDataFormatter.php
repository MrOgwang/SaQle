<?php
namespace SaQle\Orm\Entities\Field\Formatter;

class LowercaseDataFormatter extends IDataFormatter{
	 public function format($value){
	 	 $value = $this->formatter->format($value);
	 	 return strtolower($value);
	 }
}
