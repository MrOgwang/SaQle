<?php
namespace SaQle\Auth\Models;

use SaQle\Dao\Field\Types\{Pk, ManyToMany, TinyTextField, TextField};
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Model\Schema\{Model, TableInfo};
use SaQle\Auth\Models\Schema\PermissionSchema;

class BaseRole extends Model{
	protected function model_setup(TableInfo $meta) : void{
		$meta->fields = [
			 'role_id'          => new Pk(),
		     'role_name'        => new TinyTextField(required: true, strict: false),
		     'role_description' => new TextField(required: false, strict: false),
		     'permissions'      => new ManyToMany(fmodel: PERMISSION_MODEL_CLASS, pk: 'role_id', fk: 'role_id'),
		     'users'            => new ManyToMany(fmodel: AUTH_MODEL_CLASS, pk: 'role_id', fk: 'role_id')
		];
	}
}
?>