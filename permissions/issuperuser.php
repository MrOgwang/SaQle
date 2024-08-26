<?php
namespace SaQle\Permissions;

class IsSuperUser extends Permission{
	 public function has_permission() : bool{
		 $user = $this->request->session->get('user', '');
		 return $user && $user->label === 'SUPER';
	 }
}
?>