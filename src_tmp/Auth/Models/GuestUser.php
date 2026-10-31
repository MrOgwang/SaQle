<?php
namespace SaQle\Auth\Models;

use SaQle\Auth\Identity\User\Interfaces\{
	 UserInterface,
	 ActorInterface
};

class GuestUser implements UserInterface, ActorInterface {
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

	 public function actor_id() : mixed {
	 	 return null;
	 }
}
