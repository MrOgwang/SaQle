<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Field\Types\{Pk, ManyToMany, TinyTextField, TextField};
use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Model\Schema\{Model, TableInfo};

class BasePermission extends Model{
	 protected function model_setup(TableInfo $meta) : void {
		 $meta->fields = [
			 'permission_id'          => new Pk(),
		     'permission_name'        => new TinyTextField(required: true, strict: false),
		     'permission_description' => new TextField(required: false, strict: false),
		     'roles'                  => new ManyToMany(fmodel: ROLE_MODEL_CLASS, pk: 'permission_id', fk: 'permission_id', through: ROLE_PERMISSION_MODEL_CLASS),
		     'users'                  => new ManyToMany(fmodel: AUTH_MODEL_CLASS, pk: 'permission_id', fk: 'permission_id', through: USER_PERMISSION_MODEL_CLASS)
		 ];
	 }
}
?>