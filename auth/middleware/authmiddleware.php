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
use SaQle\Dao\Model\Exceptions\NullObjectException;

class AuthMiddleware extends IMiddleware{
      private function report_exception(StatusCode $c, string $m){
           (new ApiRequestProcessor())->process(new HttpMessage(code: $c, message: $m));
      }

      public function handle(MiddlewareRequestInterface &$request){
           $this->authenticate_jwt_token($request);
     	 parent::handle($request);
      }

      public function authenticate_jwt_token(MiddlewareRequestInterface $request): bool{
           if(!array_key_exists("HTTP_AUTHORIZATION", $_SERVER) && !$request->enforce_permissions){
               return true;
           }

           if(!array_key_exists("HTTP_AUTHORIZATION", $_SERVER) && $request->enforce_permissions){
               $this->report_exception(StatusCode::UNAUTHORIZED, "No authorization headers found in request");
               return false;
           }

           if(!preg_match("/^Bearer\s+(.*)$/", $_SERVER["HTTP_AUTHORIZATION"], $matches)){
                $this->report_exception(StatusCode::BAD_REQUEST, "incomplete authorization header");
                return false;
           }

           try{
                $data = (new Jwt(JWT_KEY))->decode($matches[1]);

                //use the auth model class to inject user object into request.
                $auth_model_class = AUTH_MODEL_CLASS;
                $auth_model = $auth_model_class::get_associated_model_class();
                $user = $auth_model::db()->where('user_id', $data['user_id'])->first();
                $request->user = $user;
                //(new ApiRequestProcessor())->process(new HttpMessage(code: StatusCode::OK, message: 'success', response: $user));
           }catch(NullObjectException $e){
               $this->report_exception(StatusCode::BAD_REQUEST, "Invalid credentials");
               return false;
           }catch(\Exception $e){
                $this->report_exception(StatusCode::BAD_REQUEST, $e->getMessage());
                return false;
           }

           return true;
      }
}
?>