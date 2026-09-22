<?php

namespace SaQle\Listeners\Model;

use SaQle\Core\Migration\Models\Migration;
use SaQle\Core\Events\GenericEvent;
use SaQle\Build\Commands\Migrate;

class RunTenantMigrations {

     public function handle(GenericEvent $event): void {
         new Migrate()->migrate();
     }
}
