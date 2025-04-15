<?php
declare(strict_types = 1);

namespace SaQle\Orm\Database;

use SaQle\Core\Assert\Assert;
use SaQle\Orm\Connection\Connection;
use SaQle\Orm\Database\Attributes\TransactionOutput;
use ReflectionFunction;
use Exception;

class Db{

	 public function __construct(private string $dbclass){

	 }

	 public function transaction(array $callbacks){
         try{
         	 Assert::allIsCallable($callbacks);

		 	 $pdo = resolve(Connection::class, DB_CONTEXT_CLASSES[$this->dbclass]);
             if($pdo && !$pdo->inTransaction()){
                 $pdo->beginTransaction();
             }

		 	 //array to hold the results of each callback, indexed by parameter names
	         $results = [];

             foreach($callbacks as $callback){
                 //get parameter names using Reflection
                 $reflection = new ReflectionFunction($callback);
                 $attributes = $reflection->getAttributes(TransactionOutput::class);
                 $params = [];

                 foreach($reflection->getParameters() as $param){
                     $name = $param->getName();
                     $params[] = $results[$name] ?? null;
                 }

                 //execute the callback with resolved parameters
                 $result = $callback(...$params);

                 //store the result in the context using function name as key
                 $context[$reflection->getName()] = $result;

                 if(!empty($attributes)){
                     $output_name = $attributes[0]->newInstance()->name;
                     $results[$output_name] = $result;
                 }
             }

             if($pdo && $pdo->inTransaction()){
                 $pdo->commit();
             }
             return $results;
         }catch(Exception $e){
             if($pdo && $pdo->inTransaction()){
                 $pdo->rollback();
             }
             throw $e;
         }
	 }
}
?>