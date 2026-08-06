<?php
namespace SaQle\Resource\Components\ResourceView;

use SaQle\Http\Response\Message;
use SaQle\Core\Ui\Details\DetailView;

class ResourceView {

	 public function get(int | string $id) : Message {

	 	 $model_parts = explode("@", request()->route->model_class);
         $model = $model_parts[0] ?? "";

         $panel = new DetailView(
         	 $model,
         	 [
         	 	 'id' => $id,
         	 	 'with_audit' => true
         	 ]
         );

		 return Message::ok([
		 	 'panel' => $panel
		 ]);
	 }
}