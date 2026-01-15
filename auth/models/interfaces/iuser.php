<?php 

namespace SaQle\Auth\Models\Interfaces;

interface IUser {
	 //check if a user can perform an action
	 public function check(string $action) : bool;

     //check if a user cannot perform an action
	 public function authorize(string $action) : bool;

	 public function is_guest() : bool;
}

