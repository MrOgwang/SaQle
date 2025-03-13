<?php
 namespace SaQle\Orm\Entities\Model\Manager;

 use SaQle\Orm\Commands\Crud\{SelectCommand, InsertCommand, DeleteCommand, UpdateCommand, TotalCommand, TableCreateCommand, RunCommand, TableDropCommand};
 use SaQle\Orm\Operations\Crud\{SelectOperation, InsertOperation, DeleteOperation, UpdateOperation, TotalOperation, TableCreateOperation, RunOperation, TableDropOperation};
 use SaQle\Orm\Entities\Model\Exceptions\NullObjectException;
 use function SaQle\Exceptions\{modelnotfoundexception};
 use SaQle\Orm\Entities\Model\Schema\Model;
 use SaQle\Image\Image;
 use SaQle\Commons\{DateUtils, UrlUtils, StringUtils};
 use SaQle\Services\Container\ContainerService;
 use SaQle\Services\Container\Cf;
 use SaQle\Orm\Entities\Field\Relations\{One2One, Many2Many};
 use SaQle\Orm\Entities\Model\Manager\Handlers\{TypeCast, FormattedChecker};
 use SaQle\Core\Chain\{Chain, DefaultHandler};
 use SaQle\Orm\Entities\Model\Manager\Trackers\EagerTracker;
 use SaQle\Core\Assert\Assert;
 use Closure;
 use SaQle\Orm\Entities\Model\Manager\Modes\FetchMode;
 use SaQle\Orm\Entities\Model\TempId;
 use SaQle\Orm\Entities\Model\Interfaces\{IThroughModel, ITempModel};
 use SaQle\Orm\Entities\Model\Collection\ModelCollection;

class ModelManager extends IModelManager{
	 use DateUtils, UrlUtils, StringUtils;

	 //return all the rows found
	 public function all(){
	 	 return $this->get();
	 }

	 //return the first row if its available otherwise throw an exception
	 public function first(){
	 	 $response = $this->get();
	 	 if(!$response){
	 	 	$table = $this->ctxtracker->find_table_name(0);
	 	 	throw new NullObjectException(table: $table);
	 	 }
	 	 return $response[0];
	 }

     //return the first row if its available otherwise return null
	 public function first_or_default(){
	 	 $response = $this->get();
	 	 return $response ? $response[0] : null;
	 }

     //reteurn the last row if its available otherwise throw an exception
	 public function last(){
	 	 $response = $this->get();
	 	 if(!$response){
	 	 	throw NullObjectException(table: $this->ctxtracker->find_table_name(0));
	 	 }
	 	 return $response[count($response) - 1];
	 }

	 //return the last row if its available otherwise return null
	 public function last_or_default(){
	 	 $response = $this->get();
	 	 return $response ? $response[count($response) - 1] : null;
	 }

	 public function with(array|string $field, $callable = null){
	 	 return $this;
	 }

     //inserts
     public function add(array $data, bool $skip_validation = false, int $index = 0){
     	 return $this;
     }

     public function save(){
     	 return;
     }

     //deletes
     public function delete(bool $permanently = false){
     	 return true;
     }

     //updates

     /**
      * Set the data state of the object that is either being saved or updated
      * at the moment, this only happens when you initialize
      * the manager from a model.
      * */
     public function set_data_state(array $data_state){
     	 return $this;
     }

     /**
      * Set collects key => value data reperesenting field names and the new values to update, 
      * Sometimes you need to call set multiple times to have the data you would like to update
      * 
      * @param array $data.
      * */
     public function set(array $data){
     	 return $this;
     }

     public function update(bool $multiple = false, bool $force = false){
	 	 return true;
     }
}
?>