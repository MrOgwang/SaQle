<?php
namespace SaQle\Auth\Models;

use SaQle\Dao\Field\Types\{Pk, ManyToMany, TinyTextField, TextField};
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Model\Schema\{Model, TableInfo};

class BasePermission extends Model{
	protected function model_setup(TableInfo $meta) : void{
		$meta->fields = [
			 'permission_id'          => new Pk(),
		     'permission_name'        => new TinyTextField(required: true, strict: false),
		     'permission_description' => new TextField(required: false, strict: false),
		     'roles'                  => new ManyToMany(fmodel: ROLE_MODEL_CLASS, pk: 'permission_id', fk: 'permission_id'),
		     'users'                  => new ManyToMany(fmodel: AUTH_MODEL_CLASS, pk: 'permission_id', fk: 'permission_id')
		];
	}
}
?>