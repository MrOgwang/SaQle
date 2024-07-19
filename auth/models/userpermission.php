<?php
namespace SaQle\Auth\Models;

use SaQle\Dao\Field\Attributes\{PrimaryKey, TextFieldValidation, NavigationKey, ForeignKey};
use SaQle\Dao\Model\Dao;
use SaQle\Dao\Model\Attributes\{CreatorModifierFields, CreateModifyDateTimeFields, SoftDeleteFields};
use SaQle\Controllers\Forms\FieldDataSource;

#[CreatorModifierFields()]
#[CreateModifyDateTimeFields()]
#[SoftDeleteFields()]
class UserPermission extends Dao{

	 #[PrimaryKey(type: 'GUID')]
	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false, length: 100)]
	 public string $id;

	 #[FieldDataSource()]
	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false, length: 100)]
	 #[ForeignKey(pdao: UserPermission::class, fdao: Permission::class, multiple: false, include: true, pfkeys: "permission_id=>permission_id", field: "permission")]
	 public string $permission_id;

	 #[FieldDataSource()]
	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false, length: 100)]
	 public string $user_id;
}
?>