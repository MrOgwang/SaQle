<?php
declare(strict_types = 1);

namespace SaQle\Http\Response;

enum StatusCode : int {
    case PROCESSING            = 102;
    case OK                    = 200;
    case CREATED               = 201;
    case NO_CONTENT            = 204;
    case PARTIAL_CONTENT       = 206;
    case MOVED_PERMANENTLY     = 301;
    case FOUND                 = 302;
    case BAD_REQUEST           = 400;
	case UNAUTHORIZED          = 401;
    case PAYMENT_REQUIRED      = 402;
    case FORBIDDEN             = 403;
    case NOT_FOUND             = 404;
    case METHOD_NOT_ALLOWED    = 405;
    case NOT_ACCEPTABLE        = 406;
    case REQUEST_TIMEOUT       = 408;
    case CONFLICT              = 409;
    case TOO_MANY_REQUESTS     = 429;
    case INTERNAL_SERVER_ERROR = 500;
    case SERVICE_UNAVAILABLE   = 503;
}
?>