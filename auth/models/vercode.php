<?php
namespace SaQle\Auth\Models;

use SaQle\Dao\Field\Attributes\{PrimaryKey, TextFieldValidation, NumberFieldValidation, FileFieldValidation, ForeignKey, FileField};
use SaQle\Dao\Model\Dao;
use SaQle\Dao\Model\Attributes\{CreatorModifierFields, CreateModifyDateTimeFields, SoftDeleteFields};
use SaQle\DirManager\DirManager;
use SaQle\Controllers\Forms\FieldDataSource;

#[CreatorModifierFields()]
#[CreateModifyDateTimeFields()]
#[SoftDeleteFields()]
class Vercode extends Dao{

	 #[PrimaryKey(type: 'GUID')]
	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false, length: 100)]
	 public string $id;
	 
	 #[FieldDataSource()]
	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false, length: 100)]
	 public string $code;
	 
	 #[FieldDataSource()]
	 #[TextFieldValidation(is_required: true, is_strict: true, allow_null: false, allow_empty: false, length: 50)]
	 public string $code_type;

     #[FieldDataSource()]
     #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false, length: 100)]
	 public string $email;

	 #[FieldDataSource()]
	 #[NumberFieldValidation(is_required: true, is_absolute: true, allow_null: false, allow_zero: true, length: 20)]
	 public int $date_expires;
}
?>