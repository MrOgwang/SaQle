<?php
namespace SaQle\Dao\Formatter;
class CapitalizeDataFormatter extends IDataFormatter{
	 public function format($value){
	 	 $value = $this->formatter->format($value);
	 	 return ucwords($value);
	 }
}
?>