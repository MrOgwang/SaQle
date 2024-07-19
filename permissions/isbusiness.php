<?php
namespace SaQle\Permissions;

class IsBusiness extends Permission{
	 public function has_permission() : bool{
		 $tenant = $this->request->session->get('tenant', '');
		 return $tenant && $tenant->account_type === 'Business';
	 }
}
?>