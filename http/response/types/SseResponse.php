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
         header('X-Accel-Buffering: no');

         // Disable buffering
         @ini_set('output_buffering', 'off');
         @ini_set('zlib.output_compression', false);

         while(ob_get_level() > 0){
             ob_end_flush();
         }
         
         ($this->callback)();
     }
}
