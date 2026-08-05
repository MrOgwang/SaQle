<?php

namespace SaQle\Listeners\Model;

use SaQle\Core\Migration\Models\Migration;
use SaQle\Core\Events\GenericEvent;
use SaQle\Build\Commands\Migrate;

class RunTenantMigrations {

     public function handle(GenericEvent $event): void {

         $tenant = $event->context->result();

         //get tenant migration history
         $migrations = Migration::using(system_connection())->get()
         ->where('type__eq', 'tenant')
         ->where('is_migrated__eq', 1)
         ->all();

         new Migrate()->migrate_tenant($tenant, $migrations);
     }
}
