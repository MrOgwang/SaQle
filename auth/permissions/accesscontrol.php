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
 * The Exceptions attribute is to be used on controller method to list
 * all the exceptions to automatically catch when a controller method is executed.
 * 
 * This is a convenient way to avoid recurring try catch blocks inside the controller methods,
 * allowing the code inside the controller method to be short, clean and solely focused on what its
 * supposed to do.
 * 
 * Usage example:
 * 
 * #[Exceptions([
 *     InvalidArgumentException::class => HttpMessage::BAD_REQUEST, 
 *     KeyNotFoundException::class => HttpMessage::BAD_REQUEST
 * ])]
 * public function get_post() : HttpMessage {
 *     //do whatever here
 *     return new HttpMessage(HttpMessage::OK);
 * }
 * 
 * Works but, really just use the exceptions attribute
 * 
 * public function get_post() : HttpMessage {
 *     try{
 *        //do whatever here
 *        return new HttpMessage(HttpMessage::OK);
 *     }catch(InvalidArgumentException $e){
 *        return new HttpMessage(HttpMessage::BAD_REQUEST, 'error message here!');
 *     }catch(InvalidArgumentException $e){
 *        return new HttpMessage(HttpMessage::BAD_REQUEST, 'another error message here!');
 *     }
 * }
 *
 * 
 * Note: I maybe over engineering here. Only time will tell.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com><+2547 411 420 38>
 * */
namespace SaQle\Auth\Permissions;

use Attribute;
use Exception;
use SaQle\Http\Response\HttpMessage;
use SaQle\Auth\Permissions\Exceptions\{AccessDeniedException, UnauthorizedAccessException};

#[Attribute(Attribute::TARGET_METHOD)]
class AccessControl {
     //the guard to consider
     protected string $guard;

     //the guard type
     protected string $gtype = 'attr'; //attr, perm, role

     //the code if fail
     protected int $code = HttpMessage::UNAUTHORIZED;

     //the message if fail
     protected string $message = 'Access denied!';

     //the url to redirect to if failed, especially for web requests
     protected string $redirect = '';

     public function __construct(string $guard, string $gtype = 'attr', ?string $code = null, ?string $message = null, ?string $redirect = null){
         $this->guard    = $guard;
         $this->gtype    = $gtype;
         $this->code     = !is_null($code) ? $code : $this->code;
         $this->message  = !is_null($message) ? $message : $this->message;
         $this->redirect = !is_null($redirect) ? $redirect : $this->redirect;
     }
     public function enforce(){
         $result = match($this->gtype){
             'attr' => Guard::is($this->guard),
             'perm' => Guard::allow($this->guard),
             'role' => Guard::check($this->guard)
         };
         
         if(!$result)
             throw new AccessDeniedException(code: $this->code, message: $this->message, redirect: $this->redirect);

         return true;
     }
}

