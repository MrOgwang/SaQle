<?php
namespace SaQle\Permissions;

class IsAuthenticatedOrReadOnly extends Permission{
	 public function has_permission() : bool{
		 return isset($_SESSION['is_user_authenticated']) 
		 && in_array($_SERVER['REQUEST_METHOD'], $this->safe_methods) ? true : ['has_permission' => false, 'redirect_url' => $this->redirect_url];
	 }
}

?>