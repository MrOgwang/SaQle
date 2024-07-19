<?php
namespace SaQle\Controllers\Data;

class MenuItem{
	private $properties;
	public function __construct(
		private string $label,
		private string $icon,
		private string $location = 'MENU_BAR', //locations include LINE_ITEM, MENU_BAR or BOTH
		...$properties
	){
		$this->properties = $properties;
	}
	public function get_label(){
		return $this->label;
	}
	public function get_icon(){
		return $this->icon;
	}
	public function get_link(){
		return $this->properties['link'] ?? "";
	}
	public function get_properties(){
		return $this->properties;
	}
	public function get_is_line_action(){
		return $this->is_line_action;
	}
	public function get_location(){
		return $this->location;
	}
	public function construct_menu_item(){
		$link = isset($this->properties['link']) ? "{{ base_url }}".$this->properties['link'] : "#";
		$id   = isset($this->properties['id']) ? $this->properties['id'] : "";
		return "
		<a class='flex v_center' id='{$id}' href='{$link}'>
			 <span class='flex v_center'><i class='data_panel_menu_icon' data-lucide='{$this->icon}'></i></span>
			 <span class='menuitemlabel flex v_center'>{$this->label}</span>
		 </a>
		 ";
	}
}
?>