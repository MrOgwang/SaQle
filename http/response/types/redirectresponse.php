<?php
namespace SaQle\Http\Response\Types;

use SaQle\Http\Response\Response;

final class RedirectResponse extends Response {
     public function __construct(
         protected string $url,
         int $status = 302,
         array $headers = []
     ){
         parent::__construct($status, $headers);
         $this->header('Location', $url);
     }

     protected function send_content(): void {
     }
}

