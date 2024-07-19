<?php
namespace SaQle\Views;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ViewGroupSettings{
	private ?string  $title    = null;
	private ?array   $labels   = null;
	private ?array   $exclude  = null;
	private bool     $edit     = true;
	private ?array   $controls = null;
	private bool     $defaults = false;
	public function __construct(
		?string $title    = null, 
		?array  $labels   = null,
		?array  $exclude  = null,
		bool    $edit     = true,
		?array  $controls = null,
		bool    $defaults = false
	){
		$this->title    = $title;
		$this->labels   = $labels;
		$this->exclude  = $exclude;
		$this->edit     = $edit;
		$this->controls = $controls;
		$this->defaults = $defaults;
	}

	public function get_title(){
		return $this->title;
	}

	public function get_labels(){
		return $this->labels;
	}

	public function get_exclude(){
		return $this->exclude;
	}

	public function get_edit(){
		return $this->edit;
	}

	public function get_controls(){
		return $this->controls;
	}

	public function get_defaults(){
		return $this->defaults;
	}
}
?>