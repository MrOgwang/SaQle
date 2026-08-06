<?php

namespace SaQle\Resource\Components\ResourceList;

use SaQle\Http\Response\Message;
use SaQle\Core\Ui\Panels\TableView;

class ResourceList {

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

		 return Message::ok([
		 	'panel' => $panel
		 ]);
	 }
}