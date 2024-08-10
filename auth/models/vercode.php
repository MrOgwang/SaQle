<?php
namespace SaQle\Auth\Models;

use SaQle\Auth\Models\Schema\VercodeSchema;
use Morewifi\Apps\Account\Models\User;
use SaQle\Dao\Model\Model;

#[\AllowDynamicProperties]
class Vercode extends Model{

	public string $id;
	public string $code;
	public string $code_type;
	public string $email;
	public int $date_expires;
	public User $author;
	public User $modifier;
	public int $date_added;
	public int $last_modified;
	public int $deleted;
	public User $remover;
	public int $deleted_at;


	public function __construct(...$kwargs){
		parent::__construct(...$kwargs);
	}

	protected static function get_schema(){
		return VercodeSchema::state();
	}

}
?>