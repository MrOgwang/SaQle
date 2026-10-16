<?php

namespace SaQle\Http\Response\Strategies;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\Response;
use SaQle\Http\Message\Message;
use SaQle\Http\Response\Types\FileResponse;

final class FileResponseStrategy implements ResponseStrategy {
     public function build(Request $request, Message $result) : Response {
         return new FileResponse((array)$result->get_file() ?? [], $result->get_code());
     }
}
