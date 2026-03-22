<?php

namespace SaQle\Core\Support;

use SaQle\Core\Notifications\Mail\{MailManager, Mailable};

class Mail {

     public static function send(Mailable $mail) : bool {
         return $mail->send();
     }

}