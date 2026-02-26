<?php 
namespace SaQle\Auth\interfaces;

interface UserInterface {
	 //check if a user can perform an action
	 public function check(string $action) : bool;

     //check if a user cannot perform an action
	 public function authorize(string $action) : bool;

	 public function is_guest() : bool;
}

