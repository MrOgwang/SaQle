<?php
namespace SaQle\Session\Models;

use SaQle\Dao\Field\Attributes\{PrimaryKey, TextFieldValidation};
use SaQle\Dao\Model\Dao;
use SaQle\Dao\Model\Attributes\CreateModifyDateTimeFields;

#[CreateModifyDateTimeFields()]
class Session extends Dao{

	 public function __construct(...$field_values){
	 	parent::__construct(...$field_values);
	 }

	 #[PrimaryKey(type: 'GUID')]
	 public string $id;

	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false)]
	 public string $session_id;

	 #[TextFieldValidation(is_required: false, is_strict: false, allow_null: true, allow_empty: true)]
	 public string $session_data;

}
?>