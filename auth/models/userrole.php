<?php
namespace SaQle\Auth\Models;

use SaQle\Dao\Field\Attributes\{PrimaryKey, TextFieldValidation, NavigationKey, ForeignKey};
use SaQle\Dao\Model\Dao;
use SaQle\Dao\Model\Attributes\{CreatorModifierFields, CreateModifyDateTimeFields, SoftDeleteFields};
use SaQle\Controllers\Forms\FieldDataSource;

#[CreatorModifierFields()]
#[CreateModifyDateTimeFields()]
#[SoftDeleteFields()]
class UserRole extends Dao{

	 #[PrimaryKey(type: 'GUID')]
	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false, length: 100)]
	 public string $id;

	 #[FieldDataSource()]
	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false, length: 100)]
     #[ForeignKey(pdao: UserRole::class, fdao: ROLE_MODEL_CLASS, multiple: false, include: true, pfkeys: "role_id=>role_id", field: "role")]
	 public string $role_id;

	 #[FieldDataSource()]
	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false, length: 100)]
	 public string $user_id;
}
?>