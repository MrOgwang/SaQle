<?php
namespace SaQle\Http\Response\Types;

use SaQle\Http\Response\Response;
use Closure;

final class SseResponse extends Response {
     public function __construct(private Closure $callback) {
         parent::__construct(200);
     }

     protected function prepare_response() : void {
         $this->headers([
             'Content-Type' => 'text/event-stream',
             'Cache-Control' => 'no-cache',
             'Connection' => 'keep-alive',
             'X-Accel-Buffering' => 'no',
         ]);
     }

     protected function send_content() : void {
         @ini_set('output_buffering', 'off');
         @ini_set('zlib.output_compression', '0');

         ($this->callback)();
     }
}
