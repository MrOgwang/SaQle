<?php
namespace SaQle\Controllers\Helpers;

use SaQle\Http\Request\Data\Exceptions\KeyNotFoundException;
use SaQle\Orm\Entities\Field\Exceptions\FieldValidationException;
use SaQle\Http\Response\{HttpMessage, StatusCode};
use Throwable;

class ExceptionHandler{
     private static array $default_exceptions = [
         KeyNotFoundException::class     => StatusCode::BAD_REQUEST,
         FieldValidationException::class => StatusCode::BAD_REQUEST,
     ];
     
     public static function handle(Throwable $e, array $handled_exceptions){
         foreach($handled_exceptions as $exception_type => $status_code){
             if($e instanceof $exception_type){
                 return new HttpMessage(code: $status_code, message: $e->getMessage());
             }
         }

         return new HttpMessage(code: StatusCode::INTERNAL_SERVER_ERROR);
     }

     public static function get_default_exceptions(){
         return self::$default_exceptions;
     }
}
?>
