<?php
namespace SaQle\Permissions;

class IsUser extends Permission{
	 public function has_permission() : bool{
		 $user = $this->request->session->get('user', '');
		 return $user && $user->label === 'USER';
	 }
}
?>