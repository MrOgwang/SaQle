<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Model\Schema\{Model, Table};
use SaQle\Auth\Guards\Guard;
use SaQle\Auth\interfaces\UserInterface;

class BaseUser extends Model implements UserInterface {

	 protected function table_schema(Table $table) : void {

	 	 $avatar_field = Table::image_field()
	 	 ->max_size(2)
	 	 ->upload_to(function(mixed $user){
	 	 	 return saqle_dir()->path('users.avatars', $user->get_data());
	 	 }) 
	 	 ->rename_to(function(mixed $user, string $file_name, int $file_index = 0){
	 	 	 $extension = pathinfo($file_name, PATHINFO_EXTENSION);
	 	 	 return "avatar-".strtolower($user->user_id).".".$extension;
	 	 })
	 	 ->depends_on(['user_id', 'gender'])
	 	 ->resize(['max_width' => 500, 'max_height' => 500])
		 ->storage('local');

	 	 $table->primary_key('user_id');

		 $table->fields([ 
		     'first_name'   => Table::char_field()->required(),
		     'last_name'    => Table::char_field()->required(),
		     'gender'       => Table::choice_field()->choices([
			 	 'male' => 'Male', 
			 	 'female' => 'Female',
			 	 'none' => 'Prefer not to say'
			 ])->use_keys()->default('male'),
		     'username'     => Table::char_field()->required(),
		     'password'     => Table::password_field()->required(),
		     'is_superuser' => Table::boolean_field()->required(),
		     'avatar'       => $avatar_field
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
