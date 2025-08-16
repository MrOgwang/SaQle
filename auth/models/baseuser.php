<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Field\Types\{Pk, TextField, OneToMany, ManyToMany};
use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Model\Schema\{Model, TableInfo};
use SaQle\Auth\Models\Interfaces\IUser;
use SaQle\Auth\Permissions\Guard;

class BaseUser extends Model implements IUser {
	 protected function model_setup(TableInfo $meta) : void{
		 $fields = [
		 	 'user_id'    => new Pk(),
		     'first_name' => new TextField(required: true, strict: false),
		     'last_name'  => new TextField(required: true, strict: false),
		     'username'   => new TextField(required: true, strict: false),
		     'password'   => new TextField(required: true, strict: false),
		 ];

		 if(ENABLE_RBAC){
			 $fields['roles'] = new ManyToMany(fmodel: ROLE_MODEL_CLASS, pk: 'user_id', fk: 'user_id', through: USER_ROLE_MODEL_CLASS);
			 $fields['permissions'] = new ManyToMany(fmodel: PERMISSION_MODEL_CLASS, pk: 'user_id', fk: 'user_id', through: USER_PERMISSION_MODEL_CLASS);
		 }

		 if(ENABLE_MULTITENANCY){
			 $fields['tenants'] = new ManyToMany(fmodel: TENANT_MODEL_CLASS, pk: 'user_id', fk: 'user_id', through: TENANT_USER_MODEL_CLASS);
		 }

		 $meta->fields = $fields;
	 }

     //check if a user can perform an action
	 public function can(string $action, ...$args) : bool {
	 	 return Guard::allow($action, $this, ...$args);
	 }

     //check if a user cannot perform an action
	 public function cannot(string $action, ...$args) : bool {
	 	 return !Guard::allow($action, $this, ...$args);
	 }

	 //check if a user has a certain role
	 public function has(string $role, ...$args) : bool {
	 	 return Guard::check($role, $this, ...$args);
	 }

     //check if a user doesn't have a certain role
	 public function hasnot(string $role, ...$args) : bool {
	 	 return !Guard::check($role, $this, ...$args);
	 }

	 //check if a user is of certain attribute
	 public function is(string $attr, ...$args) : bool {
	 	 return Guard::is($attr, $this, ...$args);
	 }

     //check if a user is not of a certain attribute
	 public function isnot(string $attr, ...$args) : bool {
	 	 return !Guard::is($attr, $this, ...$args);
	 }

	 public function is_guest() : bool {
	 	 return false;
	 }
}
