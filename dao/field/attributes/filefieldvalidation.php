<?php
declare(strict_types = 1);
namespace SaQle\Dao\Field\Attributes;

#[\Attribute(Attribute::TARGET_PROPERTY)]
class FileFieldValidation extends FieldValidation{
	 public function __construct(
	 	 protected array $accept = null,
	 	 $allow_null             = true,
	 	 $is_required            = false
	 ){
	 	parent::__construct(allow_null: $allow_null, is_required: $is_required);
	 }
}
?>
