<?php
/**
* This is an auto generated file.
*
* The code here is designed to work as is, and must not be modified unless you know what you are doing.
*
* If you find ways that the code can be improved to enhance speed, efficiency or memory, be kind enough
* to share with the author at wycliffomondiotieno@gmail.com or +254741142038. The author will not mind a cup
* of coffee either.
*
* Commands to generate file:
* 1. php manage.php make:migrations
* 2. php manage.php make:models
* On your terminal, cd into project root and run the above commands
* 
* Models are generated behind the scense from table schemas defined by the user.
* The model provides interfaces for interacting with the database.
* */

namespace SaQle\Auth\Models;

use SaQle\Auth\Models\Schema\ContactSchema;
use SaQle\Dao\Model\Model;

#[\AllowDynamicProperties]
class Contact extends Model{

	public $contact_id;
	public $contact_type;
	public $contact_class;
	public $contact;
	public $owner_type;
	public $owner_id;
	public $author;
	public $modifier;
	public $date_added;
	public $last_modified;
	public $deleted;
	public $remover;
	public $deleted_at;


	public function __construct(...$kwargs){
		parent::__construct(...$kwargs);
	}

	public static function get_schema(){
		return ContactSchema::state();
	}

}
?>