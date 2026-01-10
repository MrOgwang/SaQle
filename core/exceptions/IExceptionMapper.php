<?php

namespace SaQle\Core\Exceptions;

use Throwable;
use SaQle\Http\Response\HttpMessage;

interface IExceptionMapper {
    public function map(Throwable $exception): HttpMessage;
}
