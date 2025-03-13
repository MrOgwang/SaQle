<?php
class XmlResponse{
     protected $data;
     protected $status;

     public function __construct($data, $status = 200){
         $this->data = $data;
         $this->status = $status;
     }

     public function send(){
         http_response_code($this->status);
         header('Content-Type: application/xml; charset=UTF-8');
        
         $xml = new SimpleXMLElement('<response/>');
         array_walk_recursive($this->data, array ($xml, 'addChild'));
         echo $xml->asXML();
     }
}
?>
