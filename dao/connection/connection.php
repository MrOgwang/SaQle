<?php
namespace SaQle\Dao\Connection;
use SaQle\Dao\DbContext\Attributes\IDbContextOptions;
use SaQle\Dao\Connection\Interfaces\IConnection;
ob_start();
class Connection implements IConnection{
	 /**
	 * An array of pdo objects
	 */
	 private static $connection = null;
	 private static $last_connection_string = "";

	 protected function __construct(){

	 }

	 protected function __clone(){

	 }

     public function __wakeup(){
        throw new \Exception("Cannot unserialize db connection!");
     }

     /**
	 * Create a new database connection instance
	 * @param IDbContextOptions: current database context options
	 */
     public static function make(IDbContextOptions $context){
     	 $connection_string = self::get_connection_string($context);
     	 if($connection_string !== self::$last_connection_string){
     	 	 $pdo = self::connect($connection_string, $context->get_username(), $context->get_password());
     	     self::$connection = $pdo;
     	     self::$last_connection_string = $connection_string;
     	 }
     	 return new self();
     }

     /**
     * Construct a connection string from the database context options
     */
	 private static function get_connection_string(IDbContextOptions $context){
		 return $context->get_type()->value.":host=".$context->get_host().";port=".$context->get_port()->value.";dbname=".$context->get_name().";";
	 }

	 /**
	 * Create the pdo connection object
	 */
	 private static function connect(string $connection_string, string $username, string $password){
	 	try{
			 $pdo = new \PDO($connection_string, $username, $password);
			 $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			 return $pdo;
		 }catch(\Exception $ex){
			 throw $ex;
		 }
	 }

	 /**
	 * Execute a database operation
	 */
	 public static function execute(string $sql, ?array $data = null, ?string $operation = null, string $prmkeytype = ""){
		 try{
		 	 $last_insert_id = null;
	 	     $response = false;
	 	     $pdo = self::$connection;

			 $pdo->beginTransaction();
			 $statement = $pdo->prepare($sql);
			 $response  = $statement->execute($data);

			 if($response === false && $pdo->inTransaction()){
				 $pdo->rollback();
			 }else{
			 	 if($operation && $operation === "insert" && $prmkeytype === "AUTO"){
			 	 	 $last_insert_id = $pdo->lastInsertId();
			 	 }
			 	 if($pdo->inTransaction()){
			 	 	 $pdo->commit();
			 	 }
			 }

			 return $last_insert_id ? ['statement' => $statement, 'last_insert_id' => $last_insert_id, 'response' => $response] : ['statement' => $statement, 'response' => $response];

	     }catch(\Exception $ex){
	     	 if($pdo && $pdo->inTransaction()){
		 	 	 $pdo->rollback();
		 	 }
			 throw $ex;
		 }
	 }
}
?>