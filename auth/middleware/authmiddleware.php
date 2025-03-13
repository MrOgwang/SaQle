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
use SaQle\Auth\Services\AuthService;
use SaQle\Auth\Observers\SigninObserver;
use SaQle\Auth\Services\Jwt;
use SaQle\Http\Response\{HttpMessage, StatusCode};
use SaQle\Http\Request\Processors\ApiRequestProcessor;
use SaQle\Orm\Entities\Model\Exceptions\NullObjectException;
use SaQle\FeedBack\FeedBack;

class AuthMiddleware extends IMiddleware{
      private function report_exception(StatusCode $c, string $m){
           (new ApiRequestProcessor())->process(new HttpMessage(code: $c, message: $m));
      }

      public function handle(MiddlewareRequestInterface &$request){
           $this->authenticate_jwt_token($request);
     	 parent::handle($request);
      }

      public function authenticate_jwt_token(MiddlewareRequestInterface $request): bool{
           $auth_backend_class = AUTH_BACKEND_CLASS;
           $service = new $auth_backend_class('jwt');
           new SigninObserver($service);
           $feedback = $service->authenticate();
           if($feedback['status'] !== FeedBack::SUCCESS)
                (new ApiRequestProcessor())->process(HttpMessage::from_feedback($feedback));

           if($feedback['status'] === FeedBack::SUCCESS && $feedback['feedback'])
                $request->user = $feedback['feedback']['user'];

           return true;
      }
}
?>