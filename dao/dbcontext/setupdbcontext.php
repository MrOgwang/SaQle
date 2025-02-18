<?php
declare(strict_types = 1);
namespace SaQle\Dao\DbContext;

use SaQle\Auth\Models\{Login, Contact, Vercode, UserRole, RolePermission, UserPermission, Permission};
use SaQle\Session\Models\Session;
use SaQle\Migration\Models\Migration;

class SetupDbContext extends DbContext{
	 static public function get_models(){
	 	 $rbac_models = [
	 	 	 'roles'               => ROLE_MODEL_CLASS,
			 'permissions'         => PERMISSION_MODEL_CLASS,
	 	 ];

	 	 $tenant_models = [
	 	 	'tenants'               => TENANT_MODEL_CLASS,
	 	 ];

	 	 $regular_models = [
	 	 	 'users'               => AUTH_MODEL_CLASS,
		     'sessions'            => Session::class,
			 'logins'              => Login::class,
			 'contacts'            => Contact::class,
			 'verificationcodes'   => Vercode::class,
			 'migrations'          => Migration::class
	 	 ];

	 	 if(ENABLE_RBAC){
	 	 	 $regular_models = array_merge($regular_models, $rbac_models);
	 	 }

	 	 if(ENABLE_MULTITENANCY){
	 	 	 $regular_models = array_merge($regular_models, $tenant_models);
	 	 }

		 return $regular_models;
	 }
}
?>