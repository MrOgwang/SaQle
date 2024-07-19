<?php
namespace SaQle\Permissions;

class IsGaso extends Permission{
	 public function has_permission() : bool{
		 $tenant = $this->request->session->get('tenant', '');
		 return $tenant && $tenant->tenant_type === 'GASO';
	 }
}
?>