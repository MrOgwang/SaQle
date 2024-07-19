<?php
namespace SaQle\Permissions;

class IsGuest extends Permission{
	 public function __construct(...$setup_info){
		 parent::__construct(...$setup_info);
	 }
	 public function has_permission() : bool{
		 return isset($_SESSION['is_user_authenticated']) 
		 && $_SESSION['is_guest_user'] ? true : ['has_permission' => false, 'redirect_url' => $this->redirect_url];
	 }
}
?>