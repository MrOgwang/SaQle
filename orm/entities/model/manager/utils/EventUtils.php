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

trait EventUtils {
	 private function get_model_name(string $model_class_name){
	 	 $parts = explode('\\', $model_class_name);
         return end($parts);
	 }

	 protected function dispatch_event(string $model, string $phase, array $named_args, ?BaseUser $user = null, mixed $result = null){
	 	 $context = new EventContext(
             service : null,
             method  : $phase,
             args    : $named_args,
             result  : null,
             user    : $user,
             attrs: []
         );

         if(in_array($phase, [ModelEventPhase::CREATED, ModelEventPhase::UPDATED, ModelEventPhase::DELETED, ModelEventPhase::READ])){
         	 $context = $context->with_result($result);
         }

         $event = GenericEvent::named($this->get_model_name($model)."::".$phase, $context);
	 	 
	 	 //Dispatch events (from registry or static)
	 	 (new EventBus(resolve(EventRegistry::class)))->dispatch($event);
	 }

	 protected function get_named_args(string $operation, array $sql_info, ?string $table = null, ?string $model = null, ?array $data = null, ?array $files = null){
	 	 $named_args = [
	 	 	 'table'         => $table ?? $this->table, 
     	 	 'sql'           => $sql_info['sql'], 
     	 	 'prepared_data' => $sql_info['data'],
     	 	 'dbclass'       => $this->dbclass,
     	 	 'db'            => config('db_context_classes')[$this->dbclass]['name'],
     	 	 'timestamp'     => time(),
     	 	 'model'         => $model ?? $this->modelclass
	 	 ];

	 	 if($operation === 'insert' || $operation === 'update'){
	 	 	 $named_args = array_merge(['data' => $data, 'files' => $files], $named_args);
	 	 }

	 	 return $named_args;
	 }
}
