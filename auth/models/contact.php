<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Field\Types\{Pk, TinyTextField};
use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Model\Schema\{Model, TableInfo};

class Contact extends Model{
      protected function model_setup(TableInfo $meta) : void{
      	 $meta->fields = [
      	 	 'contact_id'    => new Pk(),
		     'contact_type'  => new TinyTextField(required: true, strict: true, length: 20, choices: ['email', 'phone']),
		     'contact_class' => new TinyTextField(required: true, strict: true, length: 20, choices: ['primary', 'secondary']),
		     'contact'       => new TinyTextField(required: true, length: 200),
		     'owner_type'    => new TinyTextField(required: true, strict: true, length: 20, choices: ['tenant', 'user']),
		     'owner_id'      => new TinyTextField(required: true, length: 100)
      	 ];
      	 $meta->name_property = 'contact';
      }
}
?>