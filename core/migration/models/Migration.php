<?php
namespace SaQle\Core\Migration\Models;

use SaQle\Orm\Entities\Model\Schema\{Model, Table};

class Migration extends Model {

	protected function table_schema(Table $table) : void {
		
		 $table->primary_key("migration_id");

	 	 $table->fields([
		     'migration_name' => Table::text_field()->required(),
		     'migration_timestamp' => Table::integer_field()->size('big')->required()->unsigned(),
		     'prev_migration_name' => Table::text_field(),
		     'prev_migration_timestamp' => Table::integer_field()->size('big')->unsigned(),
		     'is_migrated' => Table::boolean_field()->required()
		 ]);

		 $table->with_user_audit(false);
		 $table->with_timestamps(false);
		 $table->with_soft_delete(false);
		 
	 }

}
