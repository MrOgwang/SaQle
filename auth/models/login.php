<?php
namespace SaQle\Auth\Models;

use SaQle\Auth\Models\Schema\LoginSchema;
use Morewifi\Apps\Account\Models\User;
use SaQle\Dao\Model\Model;

#[\AllowDynamicProperties]
class Login extends Model{

	public string $login_id;
	public int $login_count;
	public int $login_datetime;
	public int $logout_datetime;
	public int $login_span;
	public string $login_location;
	public string $login_device;
	public string $login_browser;
	public string $user_id;
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
		return LoginSchema::state();
	}

}
?>