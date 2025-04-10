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
	 public function can(string $action) : bool {
	 	 return Guard::allow($action);
	 }

     //check if a user cannot perform an action
	 public function cannot(string $action) : bool {
	 	 return !Guard::allow($action);
	 }

	 //check if a user is of a certain role
	 public function is(string $role) : bool {
	 	 return Guard::check($role);
	 }

     //check if a user is not of a certain role
	 public function isnot(string $role) : bool {
	 	 return !Guard::check($role);
	 }

	 public function is_guest() : bool {
	 	 return false;
	 }
}
?>