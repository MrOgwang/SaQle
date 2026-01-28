<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class FormattedField extends TextType implements IField{
	 //the string format
	 public private(set) ?string $format = null {
	 	 set(?string $value){
	 	 	$this->format = $value;
	 	 }

	 	 get => $this->format;
	 }

	 public function format(string $format){
	 	 $this->format = $format;
	 }
	 
}
