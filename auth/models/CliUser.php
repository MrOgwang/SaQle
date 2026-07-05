<?php
namespace SaQle\Auth\Models;

use SaQle\Auth\Identity\User\Interfaces\UserInterface;

class CliUser implements UserInterface {

	 public function __construct(
	 	 public readonly string $user_id = "saqle-cli-user",
	 	 public readonly string $first_name = "Cli",
	 	 public readonly string $last_name = "System"
	 ){}

	 public function check(string $action) : bool {
	 	 return true;
	 }

	 public function authorize(string $action) : bool {
	 	 return true;
	 }

	 public function is_guest() : bool {
	 	 return false;
	 }
}
