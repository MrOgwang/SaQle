<?php
namespace SaQle\Dao\Formatter;
class LowercaseDataFormatter extends IDataFormatter{
	 public function format($value){
	 	 $value = $this->formatter->format($value);
	 	 return strtolower($value);
	 }
}
?>