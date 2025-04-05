<?php
/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

/**
 * The auth middleware checks the http_authorization headers and authenticates api requests
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Auth\Middleware;

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Auth\Services\Jwt;
use SaQle\Http\Response\HttpMessage;
use SaQle\Http\Request\Processors\ApiRequestProcessor;
use SaQle\Orm\Entities\Model\Exceptions\NullObjectException;
use SaQle\Core\FeedBack\FeedBack;

class AuthMiddleware extends IMiddleware{
      public function handle(MiddlewareRequestInterface &$request){
           $this->authenticate_jwt_token($request);
     	 parent::handle($request);
      }

      public function authenticate_jwt_token(MiddlewareRequestInterface $request): bool {
           $service = resolve(AUTH_BACKEND_CLASS, ['jwt']);
           $fb = $service->authenticate();
           $request->user = $fb->data;

           return true;
      }
}
?>