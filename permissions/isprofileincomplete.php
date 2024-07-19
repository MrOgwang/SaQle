<?php
namespace SaQle\Permissions;

class IsProfileIncomplete extends Permission{
	 public function has_permission() : bool{
		 $tenant = $this->request->session->get('tenant', '');
		 return $tenant && (int)$tenant->profile_complete === 0 ? true : false;
	 }
}
?>