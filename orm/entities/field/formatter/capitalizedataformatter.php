<?php
namespace SaQle\Orm\Entities\Field\Formatter;

class CapitalizeDataFormatter extends IDataFormatter{
	 public function format($value){
	 	 $value = $this->formatter->format($value);
	 	 return ucwords($value);
	 }
}
?>