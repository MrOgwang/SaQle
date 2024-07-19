<?php
declare(strict_types = 1);
namespace SaQle\Dao\Field\Attributes;

use SaQle\Dao\Field\Interfaces\IValidator;

abstract class FieldValidation implements IValidator{
	 public function __construct(
	 	 protected bool   $allow_null  = true,
	 	 protected bool   $is_required = false,
	 ){
	 	if($this->is_required){
	 		$this->allow_null = false;
	 	}else{
	 		$this->allow_null = true;
	 	}
	 }
}
?>
