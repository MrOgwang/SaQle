<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Field\Types\{Pk, TextField, IntegerField, FileField, OneToOne, OneToMany, ManyToMany};
use SaQle\Orm\Entities\Model\Schema\{Model, TableInfo};

class BaseTenant extends Model{
	protected function model_setup(TableInfo $meta) : void{
		$meta->fields = [
			 'tenant_id'   => new Pk(),
		     'tenant_name' => new TextField(required: true),
		     'users'       => new ManyToMany(related_model: config('auth_model_class'), local_key: 'tenant_id', foreign_key: 'tenant_id', through: config('tenant_user_model_class'))
		];
	}
}
