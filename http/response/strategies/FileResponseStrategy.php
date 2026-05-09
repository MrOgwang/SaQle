<?php

namespace SaQle\Http\Response\Strategies;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\{
     Response, 
     Message
};
use SaQle\Http\Response\Types\FileResponse;

final class FileResponseStrategy implements ResponseStrategy {
    
     public function build(Request $request, Message $result) : Response {
        
         return new FileResponse((array)$result->data ?? [], $result->code);
         
     }
}
