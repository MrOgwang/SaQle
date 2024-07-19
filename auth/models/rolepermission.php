<?php
namespace SaQle\Auth\Models;

use SaQle\Dao\Field\Attributes\{PrimaryKey, TextFieldValidation, NavigationKey, ForeignKey};
use SaQle\Dao\Model\Dao;
use SaQle\Dao\Model\Attributes\{CreatorModifierFields, CreateModifyDateTimeFields, SoftDeleteFields};
use SaQle\Controllers\Forms\FieldDataSource;
use SaQle\Dao\Field\Controls\FormControl;
use SaQle\Dao\Field\FormControlTypes;

#[CreatorModifierFields()]
#[CreateModifyDateTimeFields()]
#[SoftDeleteFields()]
class RolePermission extends Dao{
      public function __construct(...$field_values){
     	 parent::__construct(...$field_values);
     	 $this->set_meta([
     	 	'name_property' => 'permission.permission_name'
     	 ]);
      } 

	 #[PrimaryKey(type: 'GUID')]
	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false, length: 100)]
	 public string $id;

	 #[FieldDataSource()]
	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false, length: 100)]
	 public string $role_id;

	 #[FieldDataSource()]
	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false, length: 100)]
	 #[ForeignKey(pdao: RolePermission::class, fdao: Permission::class, multiple: false, include: true, pfkeys: "permission_id=>permission_id", field: "permission")]
	 public string $permission_id;
}
?>