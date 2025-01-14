<?php
namespace SaQle\Permissions;

class IsActivated extends Permission{
	 public function has_permission() : bool{
		 return $this->request->user && $this->request->user->account_status === 1;
	 }
}

?>