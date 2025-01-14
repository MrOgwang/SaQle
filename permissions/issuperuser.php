<?php
namespace SaQle\Permissions;

class IsSuperUser extends Permission{
	 public function has_permission() : bool{
		 return $this->request->user && $this->request->user->label === 'SUPER';
	 }
}
?>