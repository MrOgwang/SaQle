<?php

namespace SaQle\Http\Response\Strategies;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\Response;
use SaQle\Http\Message\Message;
use SaQle\Http\Response\Types\RedirectResponse;
use SaQle\Core\Support\Session;

final class RedirectResponseStrategy implements ResponseStrategy {

     private function apply_flash(Message $result) : void { 

         $data = $result->get_data();

         foreach($data as $key => $value){
             Session::flash($key, $value);
         }

         $message = trim($result->get_message());
         $notification = $result->get_notification();

         if($notification){
             Session::flash('__notification', $notification);
         }elseif($message){
             Session::flash('__notification', [
                 'type' => 'info',
                 'message' => $message
             ]);
         }

     }
    
     public function build(Request $request, Message $result) : Response {

         print_r($result);

         $this->apply_flash($result);

         return new RedirectResponse((string)$result->get_data()['url']);
     }
}