<?php
namespace SaQle\Http\Request\Data\Sources;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class FromBody extends From{
	 public protected(set) ?array $fields = null {
	 	 set(?array $value){
	 	 	 $this->fields = $value;
	 	 }

	 	 get => $this->fields;
	 }

	 public protected(set) bool $embedded = false {
	 	 set(bool $value){
	 	 	 $this->embedded = $value;
	 	 }

	 	 get => $this->embedded;
	 }

	 public function __construct(?string $type = null, ?array $props = null, ?string $refkey = null, ?array $fields = null, bool $embedded = false){
	 	 parent::__construct($type, $props, $refkey);

	 	 if($fields)
	 	     $this->fields = $fields;

	 	 $this->embedded = $embedded;
	 }
}
?>