<?php
namespace SaQle\Auth\Models;

use SaQle\Auth\Models\Schema\RolePermissionSchema;
use SaQle\Dao\Model\Model;

#[\AllowDynamicProperties]
class RolePermission extends Model{

	public string $id;
	public string $role_id;
	public int $date_added;
	public int $last_modified;
	public int $deleted;
	public int $deleted_at;


	public function __construct(...$kwargs){
		parent::__construct(...$kwargs);
	}

	protected static function get_schema(){
		return RolePermissionSchema::state();
	}

}
?>