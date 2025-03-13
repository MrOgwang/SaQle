<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Field\Types\{Pk, TextField, OneToMany, ManyToMany};
use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Model\Schema\{Model, TableInfo};

class BaseUser extends Model{
	protected function model_setup(TableInfo $meta) : void{
		 $fields = [
		 	 'user_id'    => new Pk(),
		     'first_name' => new TextField(required: true, strict: false),
		     'last_name'  => new TextField(required: true, strict: false),
		     'username'   => new TextField(required: true, strict: false),
		     'password'   => new TextField(required: true, strict: false),
		     'label'      => new TextField(required: true, strict: true)
		 ];

		 if(ENABLE_RBAC){
			 $fields['roles'] = new ManyToMany(fmodel: ROLE_MODEL_CLASS, pk: 'user_id', fk: 'user_id');
			 $fields['permissions'] = new ManyToMany(fmodel: PERMISSION_MODEL_CLASS, pk: 'user_id', fk: 'user_id');
		 }

		 if(ENABLE_MULTITENANCY){
			 $fields['tenants'] = new ManyToMany(fmodel: TENANT_MODEL_CLASS, pk: 'user_id', fk: 'user_id');
		 }

		 $meta->fields = $fields;
	}
}
?>