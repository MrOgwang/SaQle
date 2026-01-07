<?php
namespace SaQle\Core\Exceptions;

use SaQle\Core\Exceptions\Base\FrameworkException;
use SaQle\Http\Response\HttpMessage;
use Throwable;

class ExceptionMapper implements ExceptionMapper {
     public function map(Throwable $exception): HttpMessage {

         if ($exception instanceof FrameworkException) {
             return new HttpMessage($exception->getCode(), $exception->get_context(), $exception->getMessage());
         }

         return new HttpMessage(HttpMessage::INTERNAL_SERVER_ERROR, $exception->getTrace(), $exception->getMessage());
     }
}

