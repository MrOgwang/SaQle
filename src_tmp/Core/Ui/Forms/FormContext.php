<?php
declare(strict_types=1);

namespace SaQle\Core\Ui\Forms;

use SaQle\Http\Kernel\RuntimeContext;
use SaQle\Core\Support\Session;

final class FormContext extends RuntimeContext {
     public function __construct(
         public readonly ?FormMessage $message = null, //the submit message. mostly available with errors
         public readonly ?array       $input   = null, //this is the data coming in from a form submit
         public readonly ?array       $errors  = null, //form validation errors, keyed by field name
         public readonly ?object      $model   = null //the object being edited for edit forms
     ){}

     static public function make(?object $model = null){
         $errors       = Session::get('__errors', []);
         $input        = Session::get('__old', []);
         $notification = Session::get('__notification');
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
