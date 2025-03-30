<?php
namespace SaQle\Permissions;

class AllowAny extends Permission{
	 public function has_permission() : bool {
		 return true;
	 }
}

?>