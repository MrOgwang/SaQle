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
