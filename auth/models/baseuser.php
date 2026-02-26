<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Model\Schema\{Model, Table};
use SaQle\Auth\Guards\Guard;
use SaQle\Auth\interfaces\UserInterface;

class BaseUser extends Model implements UserInterface {

	 protected function table_schema(Table $table) : void {

	 	 $table->primary_key('user_id');

		 $table->fields([
		     'first_name'   => char_field()->required(),
		     'last_name'    => char_field()->required(),
		     'username'     => char_field()->required(),
		     'password'     => password_field()->required(),
		     'is_superuser' => boolean_field()->required(),
		 ]);
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
