<?php
declare(strict_types=1);

namespace SaQle\Orm\Database\Transaction;

use SaQle\Core\Events\EventBus;
use SaQle\Core\Registries\EventRegistry;

final class TransactionEnvelope {
     private array $events = [];

     public function record(object $event): void {
        $this->events[] = $event;
     }

     public function commit(): void {
         foreach ($this->events as $event){
             //Dispatch events (from registry or static)
             (new EventBus(resolve(EventRegistry::class)))->dispatch($event);
         }
         
         $this->events = [];
     }

     public function rollback(): void {
         $this->events = [];
     }
}
