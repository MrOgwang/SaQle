<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Field\Types\{Pk, ManyToMany, CharField, TextField};
use SaQle\Orm\Entities\Model\Schema\{Model, TableInfo};

class BaseRole extends Model{
	 protected function model_setup(TableInfo $meta) : void {

		$meta->fields = [
			 'role_id'          => new Pk(),
		     'role_name'        => new CharField(required: true),
		     'role_description' => new TextField(),
		     'permissions'      => new ManyToMany(related_model: config('permission_model_class'), local_key: 'role_id', foreign_key: 'role_id', through: config('role_permission_model_class')),
		     'users'            => new ManyToMany(related_model: config('auth_model_class'), local_key: 'role_id', foreign_key: 'role_id', through: config('user_role_model_class'))
		];
	 }
}
