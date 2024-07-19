<?php
namespace SaQle\Views;

class ViewGroupCollection{
	 private array $groups   = [];
	 private bool  $editable = false;

	 public function get_groups(){
	 	return $groups;
	 }

	 public function get_editable(){
	 	return $this->editable;
	 }

	 public function construct_groups($object_data, $view_form_id = "", $delete_form_id = ""){
	 	$controls = "";
	 	foreach($this->groups as $group){
	 		 $controls .= $group->construct_group($object_data, $view_form_id, $delete_form_id);
	 	}
	 	return $controls;
	 }

	 public function add_group(ViewGroup $group){
	 	$this->groups[] = $group;
	 	if($group->get_editable()){
 		 	$this->editable = true;
 		 }
	 }
}
?>