<?php
namespace SaQle\Dao\Model;

use SaQle\Dao\Model\Schema\TempIdSchema;

#[\AllowDynamicProperties]
class TempId extends Model{

	public string $id;
	public function __construct(...$kwargs){
		parent::__construct(...$kwargs);
	}

	public static function get_schema(){
		return TempIdSchema::state();
	}
}
?>