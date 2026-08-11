<?php
namespace SaQle\Lib\Components\ResourceDelete;

use SaQle\Http\Response\Message;

class ResourceDelete {

	 public function delete(int | string $id) : Message {

	 	 $model_parts = explode("@", request()->route->model_class);
         $model_class = $model_parts[0] ?? "";

         $deleted = $model_class::delete()->where($model_class::get_pk_name()."__eq", $id)->now();

		 return Message::redirect(route(resource_route_name('list', $model_class)))
		 ->with_message('success', 'Deleted successfully!');
	 }
}