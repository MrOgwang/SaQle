<?php
namespace SaQle\Session\Models\Schema;

use SaQle\Dao\Field\Types\{Pk, TinyTextField, TextField};
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Model\Schema\TableSchema;

#[CreateModifyDateTimeFields()]
class SessionSchema extends TableSchema{
	 public IField $id;
	 public IField $session_id;
	 public IField $session_data;

	 public function __construct(...$kwargs){
	 	 $this->id           = new Pk(type: PRIMARY_KEY_TYPE);
		 $this->session_id   = new TinyTextField(required: true, length: 100);
		 $this->session_data = new TextField(required: false, strict: false);

	 	 $this->set_meta([
	 	 	 'soft_delete'      => false,
	 	 	 'auto_cm_fields'   => false,
	 	 	 'auto_cmdt_fields' => true
	 	 ]);

	 	 parent::__construct(...$kwargs);
	 }
}
?>