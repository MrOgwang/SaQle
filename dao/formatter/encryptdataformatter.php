<?php
namespace SaQle\Dao\Formatter;
class EncryptDataFormatter extends IDataFormatter{
	 public function format($value){
	 	 $value = $this->formatter->format($value);
	 	 return MD5($value);
	 }
}
?>