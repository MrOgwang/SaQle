<?php

namespace SaQle\Http\Response\Strategies;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\Response;
use SaQle\Http\Message\Message;

interface ResponseStrategy {
     public function build(Request $request, Message $result) : Response;
}
