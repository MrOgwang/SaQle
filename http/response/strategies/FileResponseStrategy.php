<?php

namespace SaQle\Http\Response\Strategies;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\{Response, HttpMessage};
use SaQle\Http\Response\Types\FileResponse;
use SaQle\Http\Request\Execution\ActionExecutor;

final class FileResponseStrategy implements ResponseStrategy {

     public function supports(Request $request): bool {
         return $request->expects_file();
     }

     public function build(Request $request, ?HttpMessage $result = null) : Response {

         $result = $result ?? ActionExecutor::execute($request);

         return new FileResponse($result->data, $result->code);
         
     }
}
