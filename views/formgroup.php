<?php
namespace SaQle\Views;

use SaQle\Orm\Entities\Field\Controls\FormControlCollection;

class FormGroup{
	/**
	 * The title of group
	 * @var string
	 */
	 private string $title;

	 /**
	 * The description of the group.
	 * @var string
	 */
	 private string $description;

	 /**
	 * Form controls 
	 * @var FormControlCollection
	 */
	 private FormControlCollection $controls;
	 
	 /**
	 * Create a new form group instance
	 * @param string $title
	 * @param string $description
	 * @param array  $controls
	 */
	 public function __construct(string $title, string $description, FormControlCollection $controls){
		 $this->title        = $title;
		 $this->description  = $description;
		 $this->controls     = $controls;
	 }

	 public function get_title(){
	 	return $this->title;
	 }

	 public function get_description(){
	 	return $this->description;
	 }

	 public function get_control_collection(){
	 	return $this->controls;
	 }

	 public function get_controls(){
	 	return $this->controls->get_controls();
	 }
}
?>