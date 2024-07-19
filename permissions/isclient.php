<?php
namespace SaQle\Permissions;

class IsClient extends Permission{
	 public function has_permission() : bool{
		 $tenant = $this->request->session->get('tenant', '');
		 return $tenant && $tenant->tenant_type === 'CLIENT';
	 }
}
?>