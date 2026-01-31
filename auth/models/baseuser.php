<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Field\Types\{Pk, PasswordField, CharField, OneToMany, ManyToMany};
use SaQle\Orm\Entities\Model\Schema\{Model, TableInfo};
use SaQle\Auth\Models\Interfaces\IUser;
use SaQle\Auth\Guards\Guard;

class BaseUser extends Model implements IUser {
	 protected function model_setup(TableInfo $meta) : void{
		 $fields = [
		 	 'user_id'    => new Pk(),
		     'first_name' => new CharField(required: true),
		     'last_name'  => new CharField(required: true),
		     'username'   => new CharField(required: true),
		     'password'   => new PasswordField(required: true),
		 ];

		 if(config('enable_rbac')){
			 $fields['roles'] = new ManyToMany(related_model: config('role_model_class'), local_key: 'user_id', foreign_key: 'user_id', through: config('user_role_model_class'));
			 $fields['permissions'] = new ManyToMany(related_model: config('permission_model_class'), local_key: 'user_id', foreign_key: 'user_id', through: config('user_permission_model_class'));
		 }

		 if(config('with_multitenancy')){
			 $fields['tenants'] = new ManyToMany(related_model: config('tenant_model_class'), local_key: 'user_id', foreign_key: 'user_id', through: config('tenant_user_model_class'));
		 }

		 $meta->fields = $fields;
	 }

     //check if a user passes a guard
	 public function check(string $action, ...$args) : bool {
	 	 return Guard::check($action, $this, ...$args);
	 }

	 //check if a user passes a guard and throw an error
	 public function authorize(string $role, ...$args) : bool {
	 	 return Guard::authorize($role, $this, ...$args);
	 }

	 public function is_guest() : bool {
	 	 return false;
	 }
}
