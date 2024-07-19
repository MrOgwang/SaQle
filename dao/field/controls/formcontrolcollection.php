<?php
declare(strict_types = 1);
namespace SaQle\Dao\Field\Controls;

class FormControlCollection{
	 private array $controls;
	 public function add(FormControl $control){
	 	$this->controls[] = $control;
	 }
	 public function get_controls() : array{
	 	return $this->controls;
	 }
}
?>