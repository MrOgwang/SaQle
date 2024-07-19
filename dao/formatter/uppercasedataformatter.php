<?php
namespace SaQle\Dao\Formatter;
class UppercaseDataFormatter extends IDataFormatter{
	 public function format($value){
	 	 $value = $this->formatter->format($value);
	 	 return strtoupper($value);
	 }
}
?>