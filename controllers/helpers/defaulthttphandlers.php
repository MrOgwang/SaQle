<?php
namespace SaQle\Controllers\Helpers;

use SaQle\Http\Response\{HttpMessage, StatusCode};

trait DefaultHttpHandlers{
	 /**
      * Default post method implementation.
      * @return HttpMessage
      * */
	 public function post() : HttpMessage{
	 	 //do nothing
	 	 return new HttpMessage(StatusCode::OK);
	 }

	 /**
	  * Default get method implementation
	  * @return HttpMessage
	  * */
	 public function get() : HttpMessage{
	 	 //do nothing
	 	 return new HttpMessage(StatusCode::OK);
	 }

	 /**
	  * Default patch method implementation
	  * @return HttpMessage
	  * */
	 public function patch() : HttpMessage{
	 	 //do nothing
	 	 return new HttpMessage(StatusCode::OK);
	 }

	 /**
	  * Default delete method implementation
	  * @return HttpMessage
	  * */
	 public function delete() : HttpMessage{
	 	 //do nothing
	 	 return new HttpMessage(StatusCode::OK);
	 }
}
?>