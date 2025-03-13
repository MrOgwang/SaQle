<?php
namespace SaQle\Http\Request\Data\Sources;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class FromDb extends From{
	 public protected(set) ?string $field = null {
	 	 set(?string $value){
	 	 	 $this->field = $value;
	 	 }

	 	 get => $this->field;
	 }

	 public protected(set) ?string $source = null {
	 	 set(?string $value){
	 	 	 $this->source = $value;
	 	 }

	 	 get => $this->source;
	 }

	 public function __construct(?string $type = null, ?array $props = null, ?string $refkey = null, ?string $field = null, ?string $source = null){
	 	 parent::__construct($type, $props, $refkey);

	 	 if($field)
	 	     $this->field = $field;

	 	 if($source)
	 	     $this->source = $source;
	 }
}
?>