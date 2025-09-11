<?php
declare(strict_types = 1);

namespace SaQle\Orm\Entities\Model\Manager;

use SaQle\Orm\Operations\Crud\DeleteOperation;
use SaQle\Orm\Connection\Connection;
use SaQle\Orm\Database\Trackers\DbContextTracker;
use SaQle\Core\Observable\{Observable, ConcreteObservable};
use SaQle\Core\FeedBack\FeedBack;
use SaQle\Orm\Entities\Model\Observer\ModelObserver;
use SaQle\Orm\Entities\Model\Interfaces\IOperationManager;
use SaQle\Orm\Entities\Model\Manager\Utils\ObserverUtils;
use Exception;

class TruncateManager implements Observable, IOperationManager {
	 use ObserverUtils, ConcreteObservable {
		 ConcreteObservable::__construct as private __coConstruct;
	 }

	 private ?string $table = null {
	 	 set(?string $value){
	 	 	 $this->table = $value;
	 	 }

	 	 get => $this->table;
	 }

	 private ?string $dbclass = null {
	 	 set(?string $value){
	 	 	 $this->dbclass = $value;
	 	 }

	 	 get => $this->dbclass;
	 }

	 private ?string $modelclass = null {
	 	 set(?string $value){
	 	 	 $this->modelclass = $value;
	 	 }

	 	 get => $this->modelclass;
	 }

	 public function __construct(string $modelclass){
	 	 [$dbclass, $table] = $modelclass::get_table_n_dbcontext();
	 	 
	 	 if(!$table || !$dbclass || !$modelclass)
	 	 	 throw new \Exception('Cannot instantiate delete manager! Unknown model.');

	 	 $this->table      = $table;
	 	 $this->dbclass    = $dbclass;
	 	 $this->modelclass = $modelclass;

		 $this->__coConstruct();
	 }

	 public function now(){
	 	 try{
	 	 	 $pdo = resolve(Connection::class, DB_CONTEXT_CLASSES[$this->dbclass]);
	 	     return $this->truncate($pdo);
	 	 }catch(Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }

     private function truncate($pdo){
     	 $sql_info = $this->get_truncate_sql_info();
	 	 $operation = new DeleteOperation(
	 	 	 sql:   $sql_info['sql'],
	 	 	 data:  null,
	 	 	 table: $this->table
	 	 );

	 	 //send a pre truncate signal to observers
	 	 $named_args = $this->get_named_args('truncate', $sql_info);
		 $this->notify_observers('before', 'truncate', $named_args);

	 	 $result = $operation->delete($pdo);

	 	 //send a post delete signal to observers
	 	 $named_args['result'] = $result;
	 	 $this->notify_observers('after', 'truncate', $named_args);

	 	 return $result;
     }

     private function get_truncate_sql_info(){
	 	 $database     = DB_CONTEXT_CLASSES[$this->dbclass]['name'];
	 	 $table        = $this->table;
		 $sql          = "TRUNCATE TABLE {$database}.{$table}";

         return ['sql' => $sql, 'data' => null];
     }
}
