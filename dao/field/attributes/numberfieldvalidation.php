<?php
declare(strict_types = 1);
namespace SaQle\Dao\Field\Attributes;

#[\Attribute(Attribute::TARGET_PROPERTY)]
class NumberFieldValidation extends ScalarFieldValidation{
	 public function __construct(
	 	 protected ?bool $is_absolute = false,
	 	 protected ?bool $allow_zero  = true,
	 	 $max                         = null,
	 	 $max_inclusive               = false,
	 	 $min                         = null,
	 	 $min_inclusive               = false,
	 	 $length                      = null,
	 	 $pattern                     = "",
	 	 $choices                     = null,
	 	 $allow_null                  = true,
	 	 $is_required                 = false
	 ){
	 	parent::__construct(max: $max, max_inclusive: $max_inclusive, min: $min, min_inclusive: $min_inclusive, length: $length, pattern: $pattern, choices: $choices, allow_null: $allow_null, is_required: $is_required);
	 }
}
?>
