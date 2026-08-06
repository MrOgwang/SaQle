<?php
namespace SaQle\Resource\Components\ResourceDelete;

use SaQle\Http\Response\Message;
use SaQle\Routes\Resources\ResourceRouteUtils;

class ResourceDelete {

	 use ResourceRouteUtils {
         ResourceRouteUtils::__construct as private __utilsConstruct;
     }

     public function __construct(){
         $this->__utilsConstruct();
     }

	 public function delete(int | string $id) : Message {

	 	 $model_parts = explode("@", request()->route->model_class);
         $model_class = $model_parts[0] ?? "";

         $deleted = $model_class::delete()->where($model_class::get_pk_name()."__eq", $id)->now();

         $resources = $this->get_resource_links();
	 	 $current_resource = $resources[$model_class] ?? null;

		 return Message::redirect($current_resource ? $current_resource->url : null)
		 ->with_message('success', 'Deleted successfully!');
	 }
}