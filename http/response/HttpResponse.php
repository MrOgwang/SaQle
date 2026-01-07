<?php

namespace SaQle\Http\Response;

abstract class HttpResponse {
     abstract public function send(): void;
}
