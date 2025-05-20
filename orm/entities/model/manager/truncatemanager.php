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
use Exception;

class TruncateManager implements Observable, IOperationManager {
	 use ConcreteObservable {
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
	 	 $preobservers = array_merge(
	 	 	 ModelObserver::get_model_observers('before', 'truncate', $this->modelclass), 
	 	 	 ModelObserver::get_shared_observers('before', 'truncate')
	 	 );
 	     $this->quick_notify(
 	     	 observers: $preobservers,
 	     	 code: FeedBack::OK, 
 	     	 data: [
 	     	 	 'table'         => $this->table, 
 	     	 	 'sql'           => $sql_info['sql'], 
 	     	 	 'prepared_data' => $sql_info['data'],
 	     	 	 'dbclass'       => $this->dbclass,
 	     	 	 'db'            => DB_CONTEXT_CLASSES[$this->dbclass]['name'],
 	     	 	 'timestamp'     => time(),
 	     	 	 'model'         => $this->modelclass
 	     	 ]
 	     );

	 	 $result = $operation->delete($pdo);

	 	 //send a post delete signal to observers
	 	 $postobservers = array_merge(
	 	 	 ModelObserver::get_model_observers('after', 'truncate', $this->modelclass), 
	 	 	 ModelObserver::get_shared_observers('after', 'truncate')
	 	 );
 	     $this->quick_notify(
 	     	 observers: $postobservers,
 	     	 code: FeedBack::OK, 
 	     	 data: [
 	     	 	 'table'         => $this->table, 
 	     	 	 'sql'           => $sql_info['sql'], 
 	     	 	 'prepared_data' => $sql_info['data'],
 	     	 	 'dbclass'       => $this->dbclass,
 	     	 	 'db'            => DB_CONTEXT_CLASSES[$this->dbclass]['name'],
 	     	 	 'timestamp'     => time(),
 	     	 	 'model'         => $this->modelclass,
 	     	 	 'result'        => $result
 	     	 ]
 	     );

	 	 return $result;
     }

     private function get_truncate_sql_info(){
	 	 $database     = DB_CONTEXT_CLASSES[$this->dbclass]['name'];
	 	 $table        = $this->table;
		 $sql          = "TRUNCATE TABLE {$database}.{$table}";

         return ['sql' => $sql, 'data' => null];
     }
}
