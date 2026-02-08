<?php
declare(strict_types = 1);

namespace SaQle\Orm\Entities\Model\Manager;

use SaQle\Orm\Database\Drivers\DbDriver;
use SaQle\Orm\Entities\Model\Schema\Model;
use SaQle\Core\Support\Db;

class QueryManager {
	 protected string   $sql = "";
	 protected ?array   $data = null;
	 protected DbDriver $dbdriver;
	 protected Model    $model;   

	 public function __construct(Model $model){
	 	 $this->model = $model;
	 	 $this->dbdriver = Db::driver($this->model->meta->connection_name);
	 }

	 public function sql(){
	 	 return $this->sql;
	 }

	 public function data(){
	 	 return $this->data;
	 }

	 public function set_sql(string $sql){
	 	 $this->sql = $sql;
	 }

	 public function set_data(?array $data = null){
	 	 $this->data = $data;
	 }

	 public function get_query_info(){
	 	 return ['sql' => $this->sql, 'data' => $this->data];
	 }

	 public function connection_name(){
	 	 return $this->model->meta->connection_name;
	 }

	 public function table_name(){
	 	 return $this->model->meta->table_name;
	 }

	 public function get_model(){
	 	 return $this->model;
	 }
}











