<?php
namespace SaQle\Http\Response\Types;

use SaQle\Http\Response\HttpResponse;
use Closure;

class SseResponse extends HttpResponse{
     public function __construct(
         private Closure $callback
     ){}

     public function send(): void {
         header('Content-Type: text/event-stream');
         header('Cache-Control: no-cache');
         header('Connection: keep-alive');
         ($this->callback)();
     }
}
