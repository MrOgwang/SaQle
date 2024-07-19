<?php
declare(strict_types = 1);
namespace SaQle\Dao\Field\Attributes;

#[\Attribute(Attribute::TARGET_PROPERTY)]
class PrimaryKey{
	 public function __construct(private string $type){

	 }
}
?>
