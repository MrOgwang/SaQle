<?php
namespace SaQle\Auth\Models\Schema;

use SaQle\Dao\Field\Attributes\{PrimaryKey, TextFieldValidation, NumberFieldValidation, ForeignKey};
use SaQle\Dao\Model\Schema\TableSchema;
use SaQle\Dao\Model\Attributes\{CreatorModifierFields, CreateModifyDateTimeFields, SoftDeleteFields};
use SaQle\Controllers\Forms\FieldDataSource;
use SaQle\Dao\Field\Controls\FormControl;
use SaQle\Dao\Field\FormControlTypes;

#[CreatorModifierFields()]
#[CreateModifyDateTimeFields()]
#[SoftDeleteFields()]
class ContactSchema extends TableSchema{

     public function __construct(...$field_values){
     	 $this->set_meta([
     	 	'name_property' => 'contact'
     	 ]);
     	 parent::__construct(...$field_values);
     }

	 #[PrimaryKey(type: 'GUID')]
	 public string $contact_id;
	 
	 #[FormControl(type: FormControlTypes::SELECT->value, label: 'Contact Type', name: 'contact_type', options: ['email' => 'Email', 'phone' => 'Phone'], required: true)]
	 #[FieldDataSource()]
	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false, choices: ['email', 'phone'])]
	 public string $contact_type;
	 
	 #[FormControl(type: FormControlTypes::SELECT->value, label: 'Contact Class', name: 'contact_class', options: ['primary' => 'Primary', 'secondary' => 'Secondary'], required: true)]
	 #[FieldDataSource()]
	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false, choices: ['primary', 'secondary'])]
	 public string $contact_class;
	 
	 #[FormControl(type: FormControlTypes::TEXT->value, label: 'Contact', name: 'contact', required: true)]
	 #[FieldDataSource()]
	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false)]
	 public string $contact;
	 
	 #[FieldDataSource()]
	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false, choices: ['tenant', 'user'])]
	 public string $owner_type;
	 
	 #[FieldDataSource()]
	 #[TextFieldValidation(is_required: true, is_strict: false, allow_null: false, allow_empty: false)]
	 public string $owner_id;
}
?>