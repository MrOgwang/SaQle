<?php
namespace SaQle\Core\Migration\Models;

use SaQle\Orm\Entities\Model\Schema\{
	 Model, 
	 Table
};

use SaQle\Orm\Entities\Model\Interfaces\ISystemModel;

class TenantMigration extends Model implements ISystemModel {

	 protected function table_schema(Table $table) : void {
		
		 $table->primary_key("migration_id");

	 	 $table->fields([
	 	 	 'tenant' => Table::one_of(config('tenancy.model_class'))->required(),
		     'migration_name' => Table::text_field()->required(),
		     'migration_timestamp' => Table::integer_field()->size('big')->required()->unsigned(),
		     'prev_migration_name' => Table::text_field(),
		     'prev_migration_timestamp' => Table::integer_field()->size('big')->unsigned(),
		     'is_migrated' => Table::boolean_field()->required(),
		     'type' => Table::text_field()->required(),
		 ]);

		 $table->with_user_audit(false);
		 $table->with_timestamps(false);
		 $table->with_soft_delete(false);
		 
	 }
}
