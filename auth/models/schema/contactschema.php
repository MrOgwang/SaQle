<?php
namespace SaQle\Auth\Models\Schema;

use SaQle\Dao\Field\Types\{Pk, TinyTextField};
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Model\Schema\TableSchema;

class ContactSchema extends TableSchema{
	 public IField $contact_id;
	 public IField $contact_type;
	 public IField $contact_class;
	 public IField $contact;
	 public IField $owner_type;
	 public IField $owner_id;

      public function __construct(...$kwargs){
      	 $this->contact_id    = new Pk(type: PRIMARY_KEY_TYPE);
		 $this->contact_type  = new TinyTextField(required: true, strict: true, length: 20, choices: ['email', 'phone']);
		 $this->contact_class = new TinyTextField(required: true, strict: true, length: 20, choices: ['primary', 'secondary']);
		 $this->contact       = new TinyTextField(required: true, length: 200);
		 $this->owner_type    = new TinyTextField(required: true, strict: true, length: 20, choices: ['tenant', 'user']);
		 $this->owner_id      = new TinyTextField(required: true, length: 100);

     	 $this->set_meta([
     	 	'name_property' => 'contact'
     	 ]);

     	 parent::__construct(...$kwargs);
      }
}
?>