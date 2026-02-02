<?php
declare(strict_types = 1);

namespace SaQle\Orm\Entities\Model\Manager;

use SaQle\Orm\Operations\Crud\DeleteOperation;
use SaQle\Orm\Connection\Connection;
use SaQle\Orm\Database\Trackers\DbContextTracker;
use SaQle\Core\FeedBack\FeedBack;
use SaQle\Orm\Entities\Model\Interfaces\IOperationManager;
use SaQle\Orm\Entities\Model\Manager\Utils\EventUtils;
use SaQle\Core\Events\ModelEventPhase;
use SaQle\Orm\Entities\Model\Schema\Model;
use Exception;

class TruncateManager implements IOperationManager {
	 use EventUtils;

	 private Model $model;

	 public function __construct(Model $model){
	 	 $this->model = $model;
	 }

	 public function now(){
	 	 try{
	 	 	 $pdo = resolve(Connection::class, config('connections')[$this->model->meta->connection_name]);
	 	     return $this->truncate($pdo);
	 	 }catch(Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }

     private function truncate($pdo){
     	 $sql_info = $this->get_sql_info();
	 	 $operation = new DeleteOperation(
	 	 	 sql:   $sql_info['sql'],
	 	 	 data:  null,
	 	 	 table: $this->model->meta->table_name
	 	 );

	 	 //send a pre truncate signal to observers
	 	 $named_args = $this->get_named_args('truncate', $sql_info);
	 	 $this->dispatch_event($this->model::class, ModelEventPhase::TRUNCATING, $named_args, resolve('request')->user);

	 	 $result = $operation->delete($pdo);

	 	 //send a post delete signal to observers
	 	 $this->dispatch_event($this->model::class, ModelEventPhase::TRUNCATED, $named_args, resolve('request')->user, $result);

	 	 return $result;
     }

     public function get_sql_info(){
	 	 $database     = config('connections')[$this->model->meta->connection_name]['database'];
	 	 $table        = $this->model->meta->table_name;
		 $sql          = "TRUNCATE TABLE {$database}.{$table}";

         return ['sql' => $sql, 'data' => null];
     }
}
