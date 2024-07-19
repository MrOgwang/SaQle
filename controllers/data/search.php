<?php
namespace SaQle\Controllers\Data;

class Search{
	private $properties;
	public function __construct(
		private string $label = 'Search...',
		private string $icon  = 'search',
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
		return $this->link;
	}
	public function get_properties(){
		return $this->properties;
	}
	public function construct_search(){
		return "
		<div class='flex form-search-group'>
			 <input type='text' placeholder='{$this->label}' autocomplete='off'/>
			 <span class='flex center'><i data-lucide='{$this->icon}'></i></span>
		 </div>
		 ";
	}
}
?>