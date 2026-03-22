<?php

use SaQle\Core\Support\Mail;
use SaQle\Core\Notifications\Mail\Mailable;

if(!function_exists('saqle_send_email')){
     function saqle_send_email(Mailable $mail) : bool {
         return Mail::send($mail);
     }
}

if(!function_exists('saqle_queue_email')){
     function saqle_queue_email(Mailable $mail, int $delay) : void {
         Mail::later($mail, $delay);
     }
}