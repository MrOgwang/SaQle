<?php
namespace SaQle\Http\Request\Data\Sources;

abstract class From {
	 
	 public protected(set) ?string $type = null {
	 	 set(?string $value){
	 	 	 $this->type = $value;
	 	 }

	 	 get => $this->type;
	 }

	 public protected(set) ?array $props = null {
	 	 set(?array $value){
	 	 	 $this->props = $value;
	 	 }

	 	 get => $this->props;
	 }

	 public protected(set) ?string $refkey = null {
	 	 set(?string $value){
	 	 	 $this->refkey = $value;
	 	 }

	 	 get => $this->refkey;
	 }

	 public function __construct(?string $type = null, ?array $props = null, ?string $refkey = null){
	 	 if($type)
	 	 	 $this->type = $type;

	 	 if($props)
	 	 	 $this->props = $props;

	 	 if($refkey)
	 	 	 $this->refkey = $refkey;
	 }
}
