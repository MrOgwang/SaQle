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
		     'roles'                  => new ManyToMany(related_model: config('role_model_class'), local_key: 'permission_id', foreign_key: 'permission_id', through: config('role_permission_model_class')),
		     'users'                  => new ManyToMany(related_model: config('auth_model_class'), local_key: 'permission_id', foreign_key: 'permission_id', through: config('user_permission_model_class'))
		 ];
	 }
}
