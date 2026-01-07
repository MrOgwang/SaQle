<?php

namespace SaQle\Core\Exceptions;

use Throwable;
use SaQle\Core\Http\DomainResult;

interface IExceptionMapper {
    public function map(Throwable $exception): DomainResult;
}
