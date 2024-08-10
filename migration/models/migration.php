<?php
namespace SaQle\Migration\Models;

use SaQle\Migration\Models\Schema\MigrationSchema;
use SaQle\Dao\Model\Model;

#[\AllowDynamicProperties]
class Migration extends Model{

	public string $migration_id;
	public string $migration_name;
	public int $migration_timestamp;
	public int $is_migrated;


	public function __construct(...$kwargs){
		parent::__construct(...$kwargs);
	}

	protected static function get_schema(){
		return MigrationSchema::state();
	}

}
?>