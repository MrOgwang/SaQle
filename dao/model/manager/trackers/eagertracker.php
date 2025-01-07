<?php
namespace SaQle\Dao\Model\Manager\Trackers;

class EagerTracker{
	private static $instance;

    private static array $loaded_models = [];

    private static array $relations = [];

	protected function __construct(){}

	protected function __clone(){}

    public function __wakeup(){
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function get() : EagerTracker{
        return self::$instance;
    }

    public static function activate(): EagerTracker{
        if (self::$instance === null){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function add_model(string $model){
    	if(!in_array($model, self::$loaded_models)){
    		self::$loaded_models[] = $model;
    	}
    }

    public static function add_relation($rel){
         self::$relations[] = $rel;
    }

    public static function is_loaded(string $model){
        return in_array($model, self::$loaded_models);
    }

    public static function reset(){
        self::$loaded_models = [];
    }

    public static function get_loaded_models(){
        return self::$loaded_models;
    }

    public static function get_relations(){
        return self::$relations;
    }

}
?>