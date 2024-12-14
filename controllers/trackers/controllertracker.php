<?php
namespace SaQle\Controllers\Trackers;

class ControllerTracker{
	private static $instance;

    /**
     * This is a top down or bottom up tree of controllers consisting of parents and children
     * as they are expected for the current request life cycle.
     * */
	private static array $controller_tree = [];

    /**
     * These are all the controllers that will be called to compose the response for the current request
     * lifecycle.
     * */
    private static array $activated_controllers = [];

    /**
     * These are all the views that will be called to compose the response for the current request
     * lifecycle.
     * */
    private static array $activated_views = [];

    /**
     * The Singleton's constructor should always be private to prevent direct
     * construction calls with the `new` operator.
     */
	protected function __construct(){}

    /**
     * Singletons should not be cloneable.
     */
	protected function __clone(){}

	/**
     * Singletons should not be restorable from strings.
     */
    public function __wakeup(){
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function get_instance(): ControllerTracker{
        if (self::$instance === null){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function add_controller(string $controller){
    	if(!in_array($controller, self::$controlerrs)){
    		self::$controllers[] = $controller;
    	}
    }

    public static function add_active_view(string $view){
        if(!in_array($view, self::$activated_views)){
            self::$activated_views[] = $view;
        }
    }

    public static function is_view_active(string $view){
        return in_array($view, self::$activated_views);
    }

    public static function get_active_views(){
        return self::$activated_views;
    }
}
?>