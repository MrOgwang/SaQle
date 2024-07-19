<?php
namespace SaQle\Permissions;

use SaQle\Http\Request\Request;

class IsAuthenticated extends Permission{
	 public function __construct(Request $request, ...$setup_info){
 	 	parent::__construct($request, ...$setup_info);
 	 	$this->redirect_url = ROOT_DOMAIN."signin/?next=".$this->request_url;
 	 }

	 public function has_permission(): bool{
	 	 return $this->request->session->get('is_user_authenticated', false);
	 }
}

?>