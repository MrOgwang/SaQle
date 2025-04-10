<?php
namespace SaQle\Controllers\Helpers;

use SaQle\Http\Request\Data\Exceptions\KeyNotFoundException;
use SaQle\Orm\Entities\Field\Exceptions\FieldValidationException;
use SaQle\Http\Response\HttpMessage;
use SaQle\Core\Exceptions\Base\FeedbackException;
use SaQle\Auth\Permissions\Exceptions\{AccessDeniedException, UnauthorizedAccessException};
use SaQle\Http\Request\Request;
use Throwable;

class ExceptionHandler{
     private static array $default_exceptions = [
         KeyNotFoundException::class        => HttpMessage::BAD_REQUEST,
         FieldValidationException::class    => HttpMessage::BAD_REQUEST,
         AccessDeniedException::class       => HttpMessage::UNAUTHORIZED,
         UnauthorizedAccessException::class => HttpMessage::UNAUTHORIZED
     ];
     
     public static function handle(Throwable $e, array $handled_exceptions, bool $is_web_request, $errresponse = null){
         $handled_exceptions = array_merge($handled_exceptions, self::$default_exceptions);
         foreach($handled_exceptions as $exception_type => $status_code){
             if($e instanceof $exception_type){
                 if($e instanceof FeedbackException && $is_web_request){
                     $redirect = $e->getRedirect();

                     if(!$redirect){ //i want the error response to be immediatley available
                         $response = array_merge($e->getData(), $errresponse ? $errresponse->get_context() : []);
                         return new HttpMessage(code: $status_code, message: $e->getMessage(), response: $response);
                     }

                     $request = Request::init();
                     $request->context->set('FeedbackException', (Object)[
                         'message' => $e->getMessage(),
                         'code'    => $e->getCode(),
                         'data'    => array_merge($e->getData(), $errresponse ? $errresponse->get_context() : [])
                     ], true);

                     return redirect(url: $redirect);
                 }
                
                 return new HttpMessage(code: $status_code, message: $e->getMessage());
             }
         }

         return new HttpMessage(
             code: $e instanceof FeedbackException ? $e->getCode() : HttpMessage::INTERNAL_SERVER_ERROR, 
             message: $e->getMessage()
         );
     }

     public static function get_default_exceptions(){
         return self::$default_exceptions;
     }
}
?>
