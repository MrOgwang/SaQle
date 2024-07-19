<?php
declare(strict_types = 1);
namespace SaQle\Dao\Field\Attributes;

abstract class ScalarFieldValidation extends FieldValidation{
	 public function __construct(
	 	 protected ?int $max           = null,
	 	 protected bool $max_inclusive = false,
	 	 protected ?int $min           = null,
	 	 protected bool $min_inclusive = false,
	 	 protected ?int $length        = null,
	 	 protected string $pattern     = "",
	 	 protected array  $choices     = null,
	 	 $allow_null                   = true,
	 	 $is_required                  = false
	 ){
	 	parent::__construct(allow_null: $allow_null, is_required: $is_required);
	 }
}
?>
