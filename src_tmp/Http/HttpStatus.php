<?php

namespace SaQle\Http;

final class HttpStatus {
     public const CONTINUE = 100;
     public const SWITCHING_PROTOCOLS = 101;
     public const PROCESSING = 102;
     public const EARLY_HINTS = 103;

     public const OK = 200;
     public const CREATED = 201;
     public const ACCEPTED = 202;
     public const NO_CONTENT = 204;
     public const PARTIAL_CONTENT = 206;

     public const MULTIPLE_CHOICES = 300;
     public const MOVED_PERMANENTLY = 301;
     public const FOUND = 302;
     public const SEE_OTHER = 303;
     public const NOT_MODIFIED = 304;
     public const TEMPORARY_REDIRECT = 307;
     public const PERMANENT_REDIRECT = 308;

     public const BAD_REQUEST = 400;
     public const UNAUTHENTICATED = 401;
     public const PAYMENT_REQUIRED = 402;
     public const UNAUTHORIZED = 403;
     public const NOT_FOUND = 404;
     public const METHOD_NOT_ALLOWED = 405;
     public const NOT_ACCEPTABLE = 406;
     public const CONFLICT = 409;
     public const GONE = 410;
     public const UNSUPPORTED_MEDIA_TYPE = 415;
     public const UNPROCESSABLE_ENTITY = 422;
     public const TOO_MANY_REQUESTS = 429;

     public const INTERNAL_SERVER_ERROR = 500;
     public const NOT_IMPLEMENTED = 501;
     public const BAD_GATEWAY = 502;
     public const SERVICE_UNAVAILABLE = 503;
     public const GATEWAY_TIMEOUT = 504;

     private const REASONS = [
         100 => 'Continue',
         101 => 'Switching Protocols',
         102 => 'Processing',
         103 => 'Early Hints',

         200 => 'OK',
         201 => 'Created',
         202 => 'Accepted',
         204 => 'No Content',
         206 => 'Partial Content',

         300 => 'Multiple Choices',
         301 => 'Moved Permanently',
         302 => 'Found',
         303 => 'See Other',
         304 => 'Not Modified',
         307 => 'Temporary Redirect',
         308 => 'Permanent Redirect',

         400 => 'Bad Request',
         401 => 'Unauthorized',
         402 => 'Payment Required',
         403 => 'Forbidden',
         404 => 'Not Found',
         405 => 'Method Not Allowed',
         406 => 'Not Acceptable',
         409 => 'Conflict',
         410 => 'Gone',
         415 => 'Unsupported Media Type',
         422 => 'Unprocessable Entity',
         429 => 'Too Many Requests',

         500 => 'Internal Server Error',
         501 => 'Not Implemented',
         502 => 'Bad Gateway',
         503 => 'Service Unavailable',
         504 => 'Gateway Timeout',
     ];

     public static function reason(int $code): string {
         return self::REASONS[$code] ?? 'Unknown Status';
     }

     public static function group(int $code): string {
         return match(true){
             $code >= 100 && $code < 200 => 'informational',
             $code >= 200 && $code < 300 => 'success',
             $code >= 300 && $code < 400 => 'redirect',
             $code >= 400 && $code < 500 => 'client_error',
             $code >= 500 && $code < 600 => 'server_error',
             default => 'unknown',
         };
     }

     public static function is_success(int $code): bool {
         return self::group($code) === 'success';
     }

     public static function is_error(int $code): bool {
         return self::group($code) === 'client_error' || self::group($code) === 'server_error';
     }
}