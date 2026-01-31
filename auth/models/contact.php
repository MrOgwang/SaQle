<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Field\Types\{Pk, ChoiceField, CharField};
use SaQle\Orm\Entities\Model\Schema\{Model, TableInfo};
use SaQle\Core\Support\CharSet;

class Contact extends Model{
      protected function model_setup(TableInfo $meta) : void{
      	 $meta->fields = [
      	 	 'contact_id'    => new Pk(),
		     'contact_type'  => new ChoiceField(required: true, length: 20, choices: [
		     	 'email' => 'Email Address', 
		     	 'phone' => 'Phone Number'
		      ]),
		     'contact_class' => new ChoiceField(required: true, length: 20, choices: [
		     	 'primary'   => 'Primary contact', 
		     	 'secondary' => 'Secondary contact'
		      ]),
		     'contact'       => new CharField(required: true, length: 200),
		     'owner_type'    => new ChoiceField(required: true, length: 20, choices: [
		     	 'tenant' => 'Organizationn owns contact', 
		     	 'user'   => 'User owns contact'
		      ]),
		     'owner_id'      => new CharField(required: true, length: 100)
      	 ];
      	 $meta->name_property   = 'contact';
      }
}
