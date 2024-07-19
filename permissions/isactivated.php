<?php
namespace SaQle\Permissions;

class IsActivated extends Permission{
	 public function has_permission() : bool{
		 $user = $this->request->session->get('user', '');
		 return $user && $user->account_status === 1;
	 }
}

?>