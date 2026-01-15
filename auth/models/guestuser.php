<?php
namespace SaQle\Auth\Models;

use SaQle\Auth\Models\Interfaces\IUser;

class GuestUser implements IUser {

	 public string $profilephoto = ROOT_DOMAIN.'static/images/layout/male.jpg';

     //check if a user can perform an action
	 public function check(string $action) : bool {
	 	 return false;
	 }

     //check if a user cannot perform an action
	 public function authorize(string $action) : bool {
	 	 return false;
	 }

	 public function is_guest() : bool {
	 	 return true;
	 }
}
