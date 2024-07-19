<?php
namespace SaQle\Routes\Manager;

use SaQle\Routes\{IRoute, RouteCollection};

class RouteManager extends IRouteManager{
	 protected IRoute $_web_routes;
	 protected IRoute $_api_routes;
	 protected array  $_selected_routes;
	 public function __construct(){
		 $this->_web_routes = new RouteCollection();
		 $this->_api_routes = new RouteCollection();
		 
		 /**
		  * Acquire project level routes.
		 */
		 $project_api_routes_path = DOCUMENT_ROOT.'/routes/api.php';
		 $project_web_routes_path = DOCUMENT_ROOT.'/routes/web.php';
		 $api_routes = file_exists($project_api_routes_path) ? require_once $project_api_routes_path : [];
		 $web_routes = file_exists($project_web_routes_path) ? require_once $project_web_routes_path : [];
		 
		 /**
		  * Acquire the route configuration files for all installed apps.
		 */
		 foreach(INSTALLED_APPS as $app){
			 $web_routes_path = DOCUMENT_ROOT.'/apps/'.$app.'/routes/web.php';
			 $api_routes_path = DOCUMENT_ROOT.'/apps/'.$app.'/routes/api.php';
			 if(file_exists($web_routes_path)){
				 $web_routes = array_merge($web_routes, require_once $web_routes_path);
			 }
			 if(file_exists($api_routes_path)){
				 $api_routes = array_merge($api_routes, require_once $api_routes_path);
			 }
		 }
		 $this->_web_routes->set($web_routes);
		 $this->_api_routes->set($api_routes);
		 $routes = $this->_web_routes->find_matches() ?: $this->_api_routes->find_matches();
		 if(!$routes) throw new \Exception('Route not found');

		 $this->_selected_routes = $routes;
	 }

	 public function get_selected_routes(){
	 	return $this->_selected_routes;
	 }
}
?>