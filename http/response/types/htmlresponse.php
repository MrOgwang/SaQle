<?php
namespace SaQle\Http\Response\Types;

use SaQle\Http\Response\Response;

final class HtmlResponse extends Response {
     public function __construct(protected string $html, int $status = 200, array $headers = []) {
         parent::__construct($status, $headers);
         $this->header('Content-Type', 'text/html; charset=UTF-8');
     }

     public function set_body(string $html): static {
         $this->html = $html;
         return $this;
     }

     public function get_body(): string {
         return $this->html;
     }

     protected function send_content(): void {
         echo $this->html;
     }
}
