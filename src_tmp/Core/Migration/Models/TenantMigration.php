<?php
namespace SaQle\Core\Migration\Models;

use SaQle\Orm\Entities\Model\Schema\{
	 Model, 
	 Table
};

class TenantMigration extends Model {

	 protected function table_schema(Table $table) : void {

	 	 $table->name('tenant_migrations');
		
		 $table->primary_key("migration_id");

	 	 $table->fields([
	 	 	 'tenant' => Table::one_of(config('tenancy.model_class'))->required(),
		     'migration_name' => Table::char_field()->required()->max_length(200),
		     'migration_timestamp' => Table::integer_field()->size('big')->required()->unsigned(),
		     'prev_migration_name' => Table::char_field()->max_length(200),
		     'prev_migration_timestamp' => Table::integer_field()->size('big')->unsigned(),
		     'connection' => Table::char_field()->required()->max_length(200),
		     'database' => Table::char_field()->required()->max_length(200),
		     'is_migrated' => Table::boolean_field()->required()
		 ]);

		 $table->with_user_audit(false);
		 $table->with_timestamps(false);
		 $table->with_soft_delete(false);
		 
	 }
}
