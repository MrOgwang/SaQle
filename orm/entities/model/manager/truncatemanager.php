<?php
declare(strict_types = 1);

namespace SaQle\Orm\Entities\Model\Manager;

use SaQle\Core\Exceptions\Model\DeleteOperationFailedException;
use SaQle\Orm\Entities\Model\Manager\Utils\EventUtils;
use SaQle\Core\Events\ModelEventPhase;
use SaQle\Orm\Entities\Model\Schema\Model;
use Exception;

class TruncateManager extends QueryManager {
	 use EventUtils;

	 public function __construct(Model $model){
	 	 parent::__construct($model);
	 	 $this->dbdriver->set_truncate_query($this);
	 }

	 public function now(){
	 	 try{
	 	 	 //connect to the database
	 	 	 $this->dbdriver->connect_with_database();

	 	 	 //get query info
	 	 	 $query_info = $this->get_query_info();

             //send a pre truncate signal to observers
	 	     $named_args = $this->get_named_args('truncate', $query_info);
	 	     $this->dispatch_event($this->model::class, ModelEventPhase::TRUNCATING, $named_args, resolve('request')->user);
             
             //execute query
             [$statement, $response] = array_values($this->dbdriver->execute($query_info['sql'], $query_info['data']));
             $error_code = $statement->errorCode();

             if($response === false || $error_code !== "00000"){
			 	 throw new DeleteOperationFailedException([
			 	 	 'table' => $this->model->meta->table_name, 
			 	 	 'statement_error_code' => $error_code
			 	 ]);
			 }

			 $result = $statement->rowCount() > 0 ? true : false;

	 	 	 //send a post delete signal to observers
	 	     $this->dispatch_event($this->model::class, ModelEventPhase::TRUNCATED, $named_args, resolve('request')->user, $result);

	 	     return $result;

	 	 }catch(Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }
}
