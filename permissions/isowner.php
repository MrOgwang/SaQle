<?php
namespace SaQle\Permissions;

class IsOwner extends Permission{
	 public function has_permission() : bool{
		 $user = $this->request->session->get('user', '');
		 return $user && $user->label === 'OWNER';
	 }
}
?>