<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Field\Types\{Pk, ManyToMany, CharField, TextField};
use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Model\Schema\{Model, TableInfo};

class BasePermission extends Model{
	 protected function model_setup(TableInfo $meta) : void {
		 $meta->fields = [
			 'permission_id'          => new Pk(),
		     'permission_name'        => new CharField(required: true),
		     'permission_description' => new TextField(),
		     'roles'                  => new ManyToMany(fmodel: config('role_model_class'), pk: 'permission_id', fk: 'permission_id', through: config('role_permission_model_class')),
		     'users'                  => new ManyToMany(fmodel: config('auth_model_class'), pk: 'permission_id', fk: 'permission_id', through: config('user_permission_model_class'))
		 ];
	 }
}
