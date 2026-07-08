<?php
namespace SaQle\Http\Response\Types;

use SaQle\Http\Response\Response;

final class XmlResponse extends Response {

     public function __construct(protected string $xml, int $status = 200, array $headers = []) {
         parent::__construct($status, $headers);
     }

     protected function prepare_response() : void {
         $this->header('Content-Type', 'application/xml; charset=UTF-8');
     }

     public function set_body(string $xml): static {
         $this->xml = $xml;
         return $this;
     }

     public function get_body(): string {
         return $this->xml;
     }

     protected function send_content(): void {
         $xml = new SimpleXMLElement('<response/>');
         array_walk_recursive($this->xml, array ($xml, 'addChild'));
         echo $xml->asXML();
     }
}

