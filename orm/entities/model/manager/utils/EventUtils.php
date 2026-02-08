<?php
namespace SaQle\Orm\Entities\Model\Manager\Utils;

use SaQle\Core\Events\{
	 GenericEvent, 
	 EventBus, 
	 EventContext
};
use SaQle\Core\Registries\EventRegistry;
use SaQle\Auth\Models\BaseUser;
use SaQle\Core\Events\ModelEventPhase;
use SaQle\Orm\Database\Transaction\TransactionContext;

trait EventUtils {
	 private function get_model_name(string $model_class_name){
	 	 $parts = explode('\\', $model_class_name);
         return end($parts);
	 }

	 protected function dispatch_event(string $model, string $phase, array $named_args, ?BaseUser $user = null, mixed $result = null){
	 	 $context = new EventContext(
             service : $model::make(),
             method  : $phase,
             args    : $named_args,
             result  : null,
             user    : $user,
             attrs: []
         );

         $defer_dispatch = false;
         
         if(in_array($phase, [ModelEventPhase::CREATED, ModelEventPhase::UPDATED, ModelEventPhase::DELETED, ModelEventPhase::READ])){
         	 $context = $context->with_result($result);
         	 $defer_dispatch = true; //dispatch once transaction commits!
         }

         /**
          * Two events are dispatched:
          * 
          * 1. One event dispatched specifically for this model and for this action. 
          * Example, when updating a user, the event will be, User::updating or User::updated
          * 
          * Listeners attached to these events will only handle them when this model and this action happens
          * 
          * 2. One event that is attached to this action.
          * Example, when updating any model, the event will be, ::updating or ::updated
          * 
          * Listerners attached to these event will handle updates on any model
          * */
         $event_one = GenericEvent::named($this->get_model_name($model)."::".$phase, $context);
         $event_two = GenericEvent::named("::".$phase, $context);

         $connection_key = $this->dbdriver->get_connection_key();

         /**
          * If we are inside a transaction, model events must not be sent out
          * immediatly until a commit happens. Record them, the transaction 
          * will send them out after the commit is comfirmed!
          * */
         if(TransactionContext::active($connection_key) && $defer_dispatch){
             TransactionContext::envelope($connection_key)->record($event_one);
             TransactionContext::envelope($connection_key)->record($event_one);
             return;
         }
	 	 
	 	 //For before model events or out of transaction events, dispatch immediatly
	 	 (new EventBus(resolve(EventRegistry::class)))->dispatch($event_one);
	 	 (new EventBus(resolve(EventRegistry::class)))->dispatch($event_two);
	 }

	 protected function get_named_args(string $operation, array $sql_info, ?string $table = null, ?string $model = null, ?array $data = null, ?array $files = null){
	 	 $named_args = [
	 	 	 'table'         => $table ?? $this->model->meta->table_name, 
     	 	 'sql'           => $sql_info['sql'], 
     	 	 'prepared_data' => $sql_info['data'],
     	 	 'connection'    => $this->model->meta->connection_name,
     	 	 'db'            => config('connections')[$this->model->meta->connection_name]['database'],
     	 	 'timestamp'     => time(),
     	 	 'model'         => $model ?? $this->model::class
	 	 ];

	 	 if($operation === 'insert' || $operation === 'update'){
	 	 	 $named_args = array_merge(['data' => $data, 'files' => $files], $named_args);
	 	 }

	 	 return $named_args;
	 }
}
