<?php
declare(strict_types = 1);
namespace SaQle\Dao\DbContext;

use SaQle\Auth\Models\{Login, Contact, Vercode, UserRole, RolePermission, UserPermission, Permission};
use SaQle\Session\Models\Session;

class SetupDbContext extends DbContext{
	 static public function get_models(){
		 return [
		     //'users'               => User::class,
		     'sessions'            => Session::class,
			 'logins'              => Login::class,
			 'contacts'            => Contact::class,
			 'verificationcodes'   => Vercode::class,
			 //'roles'               => Role::class,
			 'permissions'         => Permission::class,
			 'rolepermissions'     => RolePermission::class,
			 'userroles'           => UserRole::class,
			 'userpermissions'     => UserPermission::class,
		 ];
	 }
}
?>