<?php
namespace SaQle\Permissions;

class IsOwner extends Permission{
	 public function has_permission() : bool{
		 return $this->request->user && $this->request->user->label === 'OWNER';
	 }
}
?>