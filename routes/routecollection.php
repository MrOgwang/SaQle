<?php
declare(strict_types = 1);
namespace SaQle\Routes;

class RouteCollection extends IRoute{
       protected $routes = []; // stores routes
	 private function add(Routes\IRoute $route){
		 $this->routes[] = $route;
	 }
	 public function set(array $routes){
		 $this->routes = $routes;
	 }
	 public function find_matches() : array {
	 	 $matches = [];
		 foreach($this->routes as $r){
			 if($r->matches()){
			 	 $matches[] = $r;
			 }
		 }
		 return $matches;
	 }
	 public function get_routes(){
	 	return $this->routes;
	 }
}

?>