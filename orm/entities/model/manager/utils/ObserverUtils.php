<?php
namespace SaQle\Orm\Entities\Model\Manager\Utils;

use SaQle\Orm\Entities\Model\Observer\ModelObserver;

trait ObserverUtils {
	 protected function notify_observers(string $when, string $operation, array $named_args, ?string $model = null){
	 	 $observers = array_merge(
	 	 	 ModelObserver::get_model_observers($when, $operation, $model ?? $this->modelclass),
	 	 	 ModelObserver::get_shared_observers($when, $operation)
	 	 );

         if($observers){
         	 list($args, $args_meta) = $this->build_args($named_args);
         	 $this->quick_notify(observers: $observers, args: $args, args_meta: $args_meta);
         }
	 }

	 protected function get_named_args(string $operation, array $sql_info, ?string $table = null, ?string $model = null, ?array $data = null, ?array $files = null){
	 	 $named_args = [
	 	 	 'table'         => $table ?? $this->table, 
     	 	 'sql'           => $sql_info['sql'], 
     	 	 'prepared_data' => $sql_info['data'],
     	 	 'dbclass'       => $this->dbclass,
     	 	 'db'            => DB_CONTEXT_CLASSES[$this->dbclass]['name'],
     	 	 'timestamp'     => time(),
     	 	 'model'         => $model ?? $this->modelclass
	 	 ];

	 	 if($operation === 'insert' || $operation === 'update'){
	 	 	 $named_args = array_merge(['data' => $data, 'files' => $files], $named_args);
	 	 }

	 	 return $named_args;
	 }
}
