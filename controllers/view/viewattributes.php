<?php
namespace SaQle\Controllers\View;
use SaQle\Views\ViewGroupCollection;

class ViewAttributes{
	public string $title                     = "";
	public string $back_url                  = "";
	public ?ViewGroupCollection $view_groups = null;
	public string $delete_form_id            = "view-delete-form";
	public string $view_form_id              = "view-edit-form";
    
    public function __construct(){
    	$this->view_groups = new ViewGroupCollection();
    }
	public function process(ViewController $controller) : array{
		$edit_attributes = $controller->get_edit_attributes();
		$del_attributes  = $controller->get_delete_attributes();
		$editable        = $this->view_groups->get_editable();
		$controls        = $this->view_groups->construct_groups($controller->get_object_data(), $this->view_form_id, $this->delete_form_id);
        $save_button = "<button form='".$this->view_form_id."' class='system-info system-info-message form_button_action' type='submit'>Save changes</button>";
        $del_button  = "<button form='".$this->delete_form_id."' class='system-info system-info-danger form_button_cancel' type='submit'>Delete</button>";
        $message = "";
        if(isset($_SESSION['viewcontroller_message'])){
        	$message = $_SESSION['viewcontroller_message'];
        	unset($_SESSION['viewcontroller_message']);
        }

	 	return [
	 		'back_url'        => $this->back_url,
	 		'title'           => $this->title,
	 		'save_button'     => $editable || $edit_attributes->editable ? $save_button : "",
	 		'delete_button'   => $del_attributes->deletable ? $del_button : "",
	 		'delete_form_id'  => $this->delete_form_id,
	 		'delete_property' => $del_attributes->delete_property,
	 		'view_form_id'    => $this->view_form_id,
	 		'edit_property'   => $edit_attributes->edit_property,
	 		'message'         => $message,
	 		'view_controls'   => $controls
	 	];
	}

}
?>