<?php
declare(strict_types = 1);

namespace SaQle\Orm\Entities\Model\Manager;

use SaQle\Orm\Database\Drivers\DbDriver;
use SaQle\Orm\Entities\Model\Interfaces\IModel;
use SaQle\Core\Support\Db;
use SaQle\Orm\Entities\Model\Collection\ModelCollection;

class QueryManager {
	 protected string   $sql = "";
	 protected ?array   $data = null;
	 protected DbDriver $dbdriver;
	 protected IModel   $model;

	 public function __construct(IModel $model){
	 	 $this->model = $model;
	 	 $this->dbdriver = Db::using($this->connection_name())->driver();
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
	 	 return $this->model instanceof ModelCollection ? $this->model[0]->table->get_connection_name() : $this->model->table->get_connection_name();
	 }

	 public function table_name(){
	 	 return $this->model instanceof ModelCollection ? $this->model[0]->table->get_table_name() : $this->model->table->get_table_name();
	 }

	 public function get_model(){
	 	 return $this->model;
	 }

	 public function get_model_class(){
	 	 return $this->model instanceof ModelCollection ? $this->model[0]::class : $this->model::class;
	 }
}











