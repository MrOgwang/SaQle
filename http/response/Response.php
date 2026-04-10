<?php

namespace SaQle\Http\Response;

abstract class Response {
     abstract public function send() : void;
}
