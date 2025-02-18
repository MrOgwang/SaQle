<?php
namespace SaQle\Auth\Models;

use SaQle\Dao\Field\Types\{Pk, TextField, IntegerField, FileField, OneToOne, OneToMany, ManyToMany};
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Model\Schema\{Model, TableInfo};

class BaseTenant extends Model{
	protected function model_setup(TableInfo $meta) : void{
		$meta->fields = [
			 'tenant_id'   => new Pk(),
		     'tenant_name' => new TextField(required: true, strict: false),
		     'users'       => new ManyToMany(fmodel: AUTH_MODEL_CLASS, pk: 'tenant_id', fk: 'tenant_id')
		];
	}
}
?>