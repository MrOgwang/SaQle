<?php
declare(strict_types=1);

namespace SaQle\Core\Ui\Forms;

use SaQle\Core\Support\Session;
use SaQle\Http\Request\RuntimeContext;

final class FormContext extends RuntimeContext {
     public function __construct(
         public readonly ?FormMessage $message = null, //the submit message. mostly available with errors
         public readonly ?array       $input   = null, //this is the data coming in from a form submit
         public readonly ?array       $errors  = null, //form validation errors, keyed by field name
         public readonly ?object      $model   = null //the object being edited for edit forms
     ){}

     static public function make(?object $model = null){
         $errors       = flash_from_session('__errors', null);
         $input        = flash_from_session('__old', null);
         $notification = flash_from_session('__notification', null);
         $message      = $notification ? new FormMessage($notification['type'], $notification['message']) : null;

         if($errors && !$message){
             $message = new FormMessage(type: 'error', text: 'There were errors with your submission!');
         }

         return new self(
             message: $message,
             input:   $input,
             errors:  $errors,
             model:   $model
         );
     } 
}
