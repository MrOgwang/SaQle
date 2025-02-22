<?php
namespace SaQle\Migration\Models;

use SaQle\Dao\Field\Types\{Pk, TextField, BigIntegerField, BooleanField, FileField, OneToOne, OneToMany, ManyToMany};
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Model\Schema\{Model, TableInfo};

class Migration extends Model{
	protected function model_setup(TableInfo $meta) : void{
	 	 $meta->fields = [
		 	 'migration_id'        => new Pk(),
		     'migration_name'      => new TextField(required: true, strict: false),
		     'migration_timestamp' => new BigIntegerField(required: true, absolute: true, zero: false),
		     'is_migrated'         => new BooleanField(required: true, absolute: true, zero: true)
		 ];

		 $meta->auto_cm     = false;
		 $meta->auto_cmdt   = false;
		 $meta->soft_delete = false;
	 }
}
?>