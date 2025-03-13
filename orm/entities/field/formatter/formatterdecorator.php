<?php
namespace SaQle\Orm\Entities\Field\Formatter;

abstract class FormatterDecorator extends IDataFormatter{
	 protected IDataFormatter $_formatter;
	 public function __construct(IDataFormatter $formatter){
	 	 $this->_formatter = $formatter;
	 }
	 public function format($value){
	 	 return $this->_formatter->format();
	 }
}
?>