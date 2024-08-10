<?php
namespace SaQle\Auth\Models;

use SaQle\Auth\Models\Schema\PermissionSchema;
use SaQle\Dao\Model\Model;

#[\AllowDynamicProperties]
class Permission extends Model{

	public string $permission_id;
	public string $permission_name;
	public string $permission_description;
	public int $date_added;
	public int $last_modified;
	public int $deleted;
	public int $deleted_at;


	public function __construct(...$kwargs){
		parent::__construct(...$kwargs);
	}

	protected static function get_schema(){
		return PermissionSchema::state();
	}

}
?>