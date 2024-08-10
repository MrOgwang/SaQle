<?php
namespace SaQle\Auth\Models;

use SaQle\Auth\Models\Schema\ContactSchema;
use Morewifi\Apps\Account\Models\User;
use SaQle\Dao\Model\Model;

#[\AllowDynamicProperties]
class Contact extends Model{

	public string $contact_id;
	public string $contact_type;
	public string $contact_class;
	public string $contact;
	public string $owner_type;
	public string $owner_id;
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
		return ContactSchema::state();
	}

}
?>