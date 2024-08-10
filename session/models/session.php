<?php
namespace SaQle\Session\Models;

use SaQle\Session\Models\Schema\SessionSchema;
use Morewifi\Apps\Account\Models\User;
use SaQle\Dao\Model\Model;

#[\AllowDynamicProperties]
class Session extends Model{

	public string $id;
	public string $session_id;
	public string $session_data;
	public User $author;
	public User $modifier;
	public int $date_added;
	public int $last_modified;


	public function __construct(...$kwargs){
		parent::__construct(...$kwargs);
	}

	protected static function get_schema(){
		return SessionSchema::state();
	}

}
?>