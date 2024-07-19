<?php
namespace SaQle\Auth\Models;

use SaQle\Dao\Field\Attributes\{PrimaryKey, TextFieldValidation, NavigationKey};
use SaQle\Dao\Model\Dao;
use SaQle\Dao\Model\Attributes\{CreatorModifierFields, CreateModifyDateTimeFields, SoftDeleteFields};
use SaQle\Controllers\Forms\FieldDataSource;
use SaQle\Dao\Field\Controls\FormControl;
use SaQle\Dao\Field\FormControlTypes;

#[CreatorModifierFields()]
#[CreateModifyDateTimeFields()]
#[SoftDeleteFields()]
class Permission extends Dao{
	 public function __construct(...$field_values){
     	 parent::__construct(...$field_values);
     	 $this->set_meta([
     	 	'name_property' => 'permission_name'
     	 ]);
     }

	 #[PrimaryKey(type: 'GUID')]
	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false, length: 100)]
	 public string $permission_id;
	 
	 #[FormControl(type: FormControlTypes::TEXT->value, label: 'Permission Name', name: 'permission_name', autocomplete: false, required: true)]
	 #[FieldDataSource()]
	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false, length: 100)]
	 public string $permission_name;
	 
	 #[FormControl(type: FormControlTypes::TEXTAREA->value, label: 'Permission Description', name: 'permission_description', autocomplete: false, required: true)]
	 #[FieldDataSource()]
	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false, length: 1000)]
	 public string $permission_description;
}
?>