<?php
namespace SaQle\Http\Response\Types;

use SaQle\Http\Response\Response;

final class TextResponse extends Response {

     public function __construct(protected string $text, int $status = 200, array $headers = []) {
         parent::__construct($status, $headers);
     }

     protected function prepare_response() : void {
         $this->header('Content-Type', 'text/plain; charset=UTF-8');
     }

     public function set_text(string $text): static {
         $this->text = $text;
         return $this;
     }

     public function get_text(): string {
         return $this->text;
     }

     protected function send_content(): void {
         echo $this->text;
     }
}

