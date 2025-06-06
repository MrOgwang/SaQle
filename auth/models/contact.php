<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Field\Types\{Pk, TinyTextField};
use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Model\Schema\{Model, TableInfo};

class Contact extends Model{
      protected function model_setup(TableInfo $meta) : void{
      	 $meta->fields = [
      	 	 'contact_id'    => new Pk(),
		     'contact_type'  => new TinyTextField(required: true, strict: true, length: 20, choices: [
		     	 'email' => 'Email Address', 
		     	 'phone' => 'Phone Number'
		      ]),
		     'contact_class' => new TinyTextField(required: true, strict: true, length: 20, choices: [
		     	 'primary'   => 'Primary contact', 
		     	 'secondary' => 'Secondary contact'
		      ]),
		     'contact'       => new TinyTextField(required: true, length: 200),
		     'owner_type'    => new TinyTextField(required: true, strict: true, length: 20, choices: [
		     	 'tenant' => 'Organizationn owns contact', 
		     	 'user'   => 'User owns contact'
		      ]),
		     'owner_id'      => new TinyTextField(required: true, length: 100)
      	 ];
      	 $meta->name_property   = 'contact';
      }
}
