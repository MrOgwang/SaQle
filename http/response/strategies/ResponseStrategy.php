<?php

namespace SaQle\Http\Response\Strategies;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\{HttpResponse, HttpMessage};

interface ResponseStrategy {
     public function supports(Request $request): bool;
     public function build(Request $request, HttpMessage $result): HttpResponse;
}
