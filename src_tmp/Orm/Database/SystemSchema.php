<?php
declare(strict_types = 1);

namespace SaQle\Orm\Database;

use SaQle\Orm\Database\Schema;
use SaQle\Core\Migration\Models\{
	 Migration,
	 TenantMigration
};
use SaQle\Session\Models\Session;
use SaQle\Core\Queue\Models\{
	 FailedJob, 
	 Job, 
	 JobBatch
};
use SaQle\Auth\Models\PlatformUser;

class SystemSchema extends Schema {

	 protected function models() : array {

	 	 /**
	 	  * The order of these models listed here is important:
	 	  * 
	 	  * Migration and TenantMigration tables need to be created first
	 	  * when running initial migrations
	 	  * */

	 	 $models = [
	 	 	 Migration::class,
	  	     config('auth.model_class'),
	 	 	 Session::class,
	 	 	 FailedJob::class,
	 	 	 Job::class,
	 	 	 JobBatch::class
	 	 ];

	 	 if(config('tenancy.enabled')){
	 	 	 $models[] = config('tenancy.model_class');
	 	 	 $models[] = TenantMigration::class;
	 	 }

	 	 return $models;
	 }

}
?>