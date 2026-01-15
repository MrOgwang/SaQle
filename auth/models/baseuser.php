<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Field\Types\{Pk, TinyTextField, OneToMany, ManyToMany};
use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Model\Schema\{Model, TableInfo};
use SaQle\Auth\Models\Interfaces\IUser;
use SaQle\Auth\Guards\Guard;

class BaseUser extends Model implements IUser {
	 protected function model_setup(TableInfo $meta) : void{
		 $fields = [
		 	 'user_id'    => new Pk(),
		     'first_name' => new TinyTextField(required: true, strict: false),
		     'last_name'  => new TinyTextField(required: true, strict: false),
		     'username'   => new TinyTextField(required: true, strict: false),
		     'password'   => new TinyTextField(required: true, strict: false),
		 ];

		 if(config('enable_rbac')){
			 $fields['roles'] = new ManyToMany(fmodel: config('role_model_class'), pk: 'user_id', fk: 'user_id', through: config('user_role_model_class'));
			 $fields['permissions'] = new ManyToMany(fmodel: config('permission_model_class'), pk: 'user_id', fk: 'user_id', through: config('user_permission_model_class'));
		 }

		 if(config('enable_multitenancy')){
			 $fields['tenants'] = new ManyToMany(fmodel: config('tenant_model_class'), pk: 'user_id', fk: 'user_id', through: config('tenant_user_model_class'));
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
