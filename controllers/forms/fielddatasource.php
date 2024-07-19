<?php
namespace SaQle\Controllers\Forms;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FieldDataSource{
	private string  $name;
	private string  $source;
	private ?string $control = null;
	private         $value;
	private bool    $is_file = false;
	public function __construct(string $name = "", string $source = "defined", ?string $control = null, $value = null, bool $is_file = false){
		$this->name    = $name;
		$this->source  = $source;
		$this->control = $control;
		$this->value   = $value;
		$this->is_file = $is_file;
	}

	public function get_name(){
		return $this->name;
	}

	public function get_is_file(){
		return $this->is_file;
	}

	public function get_source(){
		return $this->source;
	}

	public function get_control(){
		return $this->control;
	}

	public function get_value(){
		return $this->value;
	}

	public function set_name(string $name) : FieldDataSource{
		$this->name = $name;
		return $this;
	}

	public function set_source(string $source) : FieldDataSource{
		$this->source = $source;
		return $this;
	}

	public function set_control(string $control) : FieldDataSource{
		$this->control = $control;
		return $this;
	}

	public function set_value($value) : FieldDataSource{
		$this->value = $value;
		return $this;
	}

	public function set_is_file(bool $is_file){
		$this->is_file = $is_file;
	}
}
?>