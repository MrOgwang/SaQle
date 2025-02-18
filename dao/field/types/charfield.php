<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Interfaces\IField;

class CharField extends TextType implements IField{
	 public function __construct(...$kwargs){
		 $kwargs['column_type'] = "CHAR";
		 $kwargs['length']      = 1;
		 $kwargs['maximum']     = 1;
		 parent::__construct(...$kwargs);
	 }
}
?>