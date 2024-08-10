<?php
namespace SaQle\Auth\Models;

use SaQle\Auth\Models\Schema\UserRoleSchema;
use SaQle\Dao\Model\Model;

#[\AllowDynamicProperties]
class UserRole extends Model{

	public string $id;
	public string $user_id;
	public int $date_added;
	public int $last_modified;
	public int $deleted;
	public int $deleted_at;


	public function __construct(...$kwargs){
		parent::__construct(...$kwargs);
	}

	protected static function get_schema(){
		return UserRoleSchema::state();
	}

}
?>