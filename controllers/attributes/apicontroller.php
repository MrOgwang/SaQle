<?php
namespace SaQle\Controllers\Attributes;

use Attribute;
use SaQle\Http\Request\Request;
use SaQle\Http\Response\{HttpMessage, StatusCode};

#[Attribute(Attribute::TARGET_CLASS)]
class ApiController{
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
	 protected function send_json_headers(){
		 header("Expires: 0");
		 header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
		 header("Cache-Control: no-store, no-cache, must-revalidate");
		 header("Cache-Control: post-check=0, pre-check=0", false);
		 header("Pragma: no-cache");
		 header("Content-Type: application/json; charset=utf-8");
	 }
	 protected function send_stream_headers(){
		 header("Cache-Control: public");
         header("Content-Description: File Transfer");
         header("Content-Disposition: attachment; filename=".$file."");
         header("Content-Transfer-Encoding: binary");
         header("Content-Type: binary/octet-stream");
	 }
	 protected function send_xml_headers(){
		 
	 }
	 protected function send_event_stream_headers(){
		 header('Content-Type: text/event-stream');
         header('Cache-Control: no-cache');
	 }
	 public function respond(){
		 $this->send_json_headers();
         $response_data    = $this->message->get_response();
         $http_status_code = (int)$this->message->get_code();
         http_response_code($http_status_code);
         if($http_status_code != 200){
             $response = [
                'HttpVerb'          => $this->message->get_method(),
                'HttpStatusCode'    => $this->message->get_code(),
                'HttpStatusMessage' => $this->message->get_status_message(),
                'Message'           => $this->message->get_message(),
                'Response'          => $response_data,
             ];
         }else{
             $response = $response_data;
         }
         print json_encode($response);
         exit;
	 }
}
?>