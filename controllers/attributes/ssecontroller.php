<?php
namespace SaQle\Controllers\Attributes;

use Attribute;
use SaQle\Http\Request\Request;
use SaQle\Http\Response\{HttpMessage, StatusCode};

#[Attribute(Attribute::TARGET_CLASS)]
class SseController{
	 /**
      * Current request object
      * @var Request
      * */
     private Request     $request;
     private HttpMessage $message;
     public function __construct(){

     }
     public function init(Request $request, HttpMessage $message){
	 	 $this->request = $request;
	 	 $this->message = $message;
	 }
	 protected function send_event_stream_headers(){
         header('Content-Type: text/event-stream');
         header('Cache-Control: no-cache');
         header('Connection: keep-alive');
	 }
	 public function send(){
		 $this->send_event_stream_headers();
         $response = $this->message->get_response();

         //Disable execution time limits to keep the connection open
	     set_time_limit(0);

	     //Output events to the client every few seconds
	     while(true){
	        //Send the event to the client
	        echo "data: ".json_encode($response)."\n\n";

	        //Flush the output buffer to ensure data is sent immediately
	        ob_flush();
	        flush();

	        //Wait for a while before sending the next event
	        sleep(2);
	     }
	 }
}
?>