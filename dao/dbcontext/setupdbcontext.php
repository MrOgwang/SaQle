<?php
declare(strict_types = 1);
namespace SaQle\Dao\DbContext;

use SaQle\Auth\Models\Schema\{LoginSchema, ContactSchema, VercodeSchema, UserRoleSchema, RolePermissionSchema, UserPermissionSchema, PermissionSchema};
use SaQle\Session\Models\Schema\SessionSchema;
use SaQle\Migration\Models\Schema\MigrationSchema;

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
		     'sessions'            => SessionSchema::class,
			 'logins'              => LoginSchema::class,
			 'contacts'            => ContactSchema::class,
			 'verificationcodes'   => VercodeSchema::class,
			 'migrations'          => MigrationSchema::class
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