<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Field\Types\{Pk, ManyToMany, TinyTextField, TextField};
use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Model\Schema\{Model, TableInfo};

class BaseRole extends Model{
	 protected function model_setup(TableInfo $meta) : void{
		$meta->fields = [
			 'role_id'          => new Pk(),
		     'role_name'        => new TinyTextField(required: true, strict: false),
		     'role_description' => new TextField(required: false, strict: false),
		     'permissions'      => new ManyToMany(fmodel: config('permission_model_class'), pk: 'role_id', fk: 'role_id', through: config('role_permission_model_class')),
		     'users'            => new ManyToMany(fmodel: config('auth_model_class'), pk: 'role_id', fk: 'role_id', through: config('user_role_model_class'))
		];
	 }
}
