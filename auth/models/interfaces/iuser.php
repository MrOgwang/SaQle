<?php 

namespace SaQle\Auth\Models\Interfaces;

interface IUser {
	 //check if a user can perform an action
	 public function can(string $action) : bool;

     //check if a user cannot perform an action
	 public function cannot(string $action) : bool;

	 //check if a user is of a certain role
	 public function is(string $role) : bool;

     //check if a user is not of a certain role
	 public function isnot(string $action) : bool;

	 public function is_guest() : bool;
}

?>