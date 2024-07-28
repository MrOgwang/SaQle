<?php
namespace SaQle\Dao\Connection;
use SaQle\Dao\DbContext\Attributes\IDbContextOptions;
use SaQle\Dao\Connection\Interfaces\IConnection;
ob_start();
class Connection implements IConnection{
	 /**
	 * A pdo connection object
	 */
	 private $pdo;
	 
	 /**
	 * Create a new database connection instance
	 * @param IDbContextOptions: current database context options
	 */
	 public function __construct(private IDbContextOptions $context){}

     /**
     * Get context options
     * @return IDbContextOptions
     */
	 public function get_context_options() : IDbContextOptions{
	 	return $this->context;
	 }

	 /**
     * Set context options
     * @param IDbContextOptions
     */
	 public function set_context_options(IDbContextOptions $context) : void{
	 	 $this->context = $context;
	 }

     /**
     * Construct a connection string from the database context options
     */
	 private function get_connection_string(){
		 return $this->context->get_type()->value.":host=".$this->context->get_host().";port=".$this->context->get_port()->value.";dbname=".$this->context->get_name().";";
	 }

	 /**
	 * Create the pdo connection object
	 */
	 private function connect(){
	 	try{
			 $connection_string = $this->get_connection_string();
			 $this->pdo = new \PDO($connection_string, $this->context->get_username(), $this->context->get_password());
			 $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		 }catch(\Exception $ex){
			 throw $ex;
		 }
	 }

	 /**
	 * Execute a database operation
	 */
	 public function execute(string $sql, ?array $data = null, ?string $operation = null, string $prmkeytype = ""){
	 	 $this->connect();
	 	 $last_insert_id = null;
	 	 $response       = false;
		 try{
			 $this->pdo->beginTransaction();
			 $statement = $this->pdo->prepare($sql);
			 $response  = $statement->execute($data);

			 if($response === false && $this->pdo->inTransaction()){
				 $this->pdo->rollback();
			 }else{
			 	 if($operation && $operation === "insert" && $prmkeytype === "AUTO"){
			 	 	 $last_insert_id = $this->pdo->lastInsertId();
			 	 }
			 	 if($this->pdo->inTransaction()){
			 	 	 $this->pdo->commit();
			 	 }
			 }
	     }catch(\Exception $ex){
	     	 if($this->pdo->inTransaction()){
		 	 	 $this->pdo->rollback();
		 	 }
			 throw $ex;
		 }
		 return $last_insert_id ? ['statement' => $statement, 'last_insert_id' => $last_insert_id, 'response' => $response] : 
		 ['statement' => $statement, 'response' => $response];
	 }
}
?>