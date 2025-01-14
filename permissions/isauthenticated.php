<?php
namespace SaQle\Permissions;

use SaQle\Http\Request\Request;

class IsAuthenticated extends Permission{
	 public function __construct(...$setup_info){
 	 	parent::__construct(...$setup_info);
 	 	$this->redirect_url = ROOT_DOMAIN."signin/?next=".$this->request_url;
 	 }

	 public function has_permission(): bool{
	 	 return $this->request->user ? true : false;
	 }
}

?>