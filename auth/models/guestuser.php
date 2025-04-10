<?php
namespace SaQle\Auth\Models;

use SaQle\Auth\Models\Interfaces\IUser;

class GuestUser implements IUser {

     //check if a user can perform an action
	 public function can(string $action) : bool {
	 	 return false;
	 }

     //check if a user cannot perform an action
	 public function cannot(string $action) : bool {
	 	 return true;
	 }

	 //check if a user is of a certain role
	 public function is(string $role) : bool {
	 	 return false;
	 }

     //check if a user is not of a certain role
	 public function isnot(string $action) : bool {
	 	 return true;
	 }

	 public function is_guest() : bool {
	 	 return true;
	 }
}
?>