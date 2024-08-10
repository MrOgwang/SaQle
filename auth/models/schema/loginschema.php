<?php
namespace SaQle\Auth\Models\Schema;

use SaQle\Dao\Field\Attributes\{PrimaryKey, TextFieldValidation, NumberFieldValidation, ForeignKey};
use SaQle\Dao\Model\Schema\TableSchema;
use SaQle\Dao\Model\Attributes\{CreatorModifierFields, CreateModifyDateTimeFields, SoftDeleteFields};

#[CreatorModifierFields()]
#[CreateModifyDateTimeFields()]
#[SoftDeleteFields()]
class LoginSchema extends TableSchema{
	
	 public function __construct(...$field_values){
	 	parent::__construct(...$field_values);
	 }

	 #[PrimaryKey(type: 'GUID')]
	 public string $login_id;
	 
	 #[NumberFieldValidation(is_required: true, is_absolute: true, allow_null: false, allow_zero: false)]
	 public int $login_count;
	 
	 #[NumberFieldValidation(is_required: true, is_absolute: true, allow_null: false, allow_zero: false)]
	 public int $login_datetime;
	 
	 #[NumberFieldValidation(is_required: false, is_absolute: true, allow_null: true, allow_zero: false)]
	 public int $logout_datetime;
	 
	 #[NumberFieldValidation(is_required: false, is_absolute: true, allow_null: true, allow_zero: false)]
	 public int $login_span;
	 
	 #[TextFieldValidation(is_required: false, is_strict: false, allow_null: true, allow_empty: true)]
	 public string $login_location;
	 
	 #[TextFieldValidation(is_required: false, is_strict: false, allow_null: true, allow_empty: true)]
	 public string $login_device;
	 
	 #[TextFieldValidation(is_required: false, is_strict: false, allow_null: true, allow_empty: true)]
	 public string $login_browser;
	 
	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false)]
	 public string $user_id;
	 
}
?>