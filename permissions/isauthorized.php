<?php
namespace SaQle\Permissions;

#[Attribute(Attribute::TARGET_CLASS)]
class IsAuthorized extends Permission{
	 protected $redirect_url = ROOT_DOMAIN."signin/";
	 public function has_permission() : bool{
	 	 /**
	 	  * User must be logged in.
	 	  * */
	 	 if(!$this->request->user){
	 	 	return false;
	 	 }

		 return true;
	 }
}
?>