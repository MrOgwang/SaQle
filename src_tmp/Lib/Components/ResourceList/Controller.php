<?php

namespace SaQle\Lib\Components\ResourceList;

use SaQle\Http\Message\Message;
use SaQle\Core\Ui\Panels\TableView;
use SaQle\Routing\Resources\ResourceRouteUtils;

class Controller {

	 use ResourceRouteUtils;
	 
     public function get(
	 	 int    $page    = 1,
	 	 int    $records = 100,
	 	 string $search  = "",
	 	 array  $filter  = []
	 ) : Message {
	 	
	 	 $panel = new TableView(
	 	 	 request()->route->model_class,
	 	 	 [
	 	 	 	 'pagination' => [
	 	 	 	     'page' => $page,
	 	 	 	     'records' => $records
	 	 	     ],
	 	 	     'search' => $search,
	 	 	     'presenter' => 'admin',
	 	 	     'with_audit' => true,
	 	 	     'filter' => $filter
	 	 	 ]
	 	 );

	 	 return Message::ok()
	 	 ->with('panel', $panel)
	 	 ->with('resource', $this->resource(request()->route->model_class));
	 }
}