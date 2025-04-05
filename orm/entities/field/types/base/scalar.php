<?php
namespace SaQle\Orm\Entities\Field\Types\Base;

abstract class Scalar extends RealField{
	 private function format_choices(array $choices, bool $use_keys = false) : array {
 	 	 return $use_keys ? array_keys($choices) : array_values($choices);
	 }

	 //an array of choices from which the value must exists
	 public ?array $choices = null {
	 	 set(?array $value){
	 	 	$this->choices = $value;
	 	 }

	 	 get => $this->choices;
	 }

	 protected bool $use_keys = false;
 
	 public function __construct(...$kwargs){
	 	 if(array_key_exists('choices', $kwargs) && is_array($kwargs['choices'])){
	 	 	 $kwargs['choices'] = $this->format_choices($kwargs['choices'], $kwargs['use_keys'] ?? false);
	 	 }
		 parent::__construct(...$kwargs);
	 }

	 protected function get_validation_kwargs() : array{
		 return array_merge(parent::get_validation_kwargs(), ['choices']);
	 }

	 protected function get_db_kwargs() : array{
		 return array_merge(parent::get_db_kwargs(), ['default']);
	 }
}
?>