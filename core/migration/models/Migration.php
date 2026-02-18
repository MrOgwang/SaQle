<?php
namespace SaQle\Core\Migration\Models;

use SaQle\Orm\Entities\Field\Types\{Pk, TextField, IntegerField, BooleanField, FileField, OneToOne, OneToMany, ManyToMany};
use SaQle\Orm\Entities\Model\Schema\{Model, TableInfo};

class Migration extends Model{
	protected function model_setup(TableInfo $meta) : void {
		
	 	 $meta->fields([
		 	 'migration_id'             => new Pk(),
		     'migration_name'           => new TextField(required: true),
		     'migration_timestamp'      => new IntegerField(size: 'big', required: true, unsigned: true),
		     'prev_migration_name'      => new TextField(required: false),
		     'prev_migration_timestamp' => new IntegerField(size: 'big', required: false, unsigned: true),
		     'is_migrated'              => new BooleanField(required: true, unsigned: true)
		 ]);
		 $meta->with_user_audit(false);
		 $meta->with_timestamps(false);
		 $meta->with_soft_delete(false);
	 }
}
