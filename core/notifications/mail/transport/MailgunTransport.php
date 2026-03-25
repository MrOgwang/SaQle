<?php

namespace SaQle\Core\Notifications\Mail\Transport;

use SaQle\Core\Support\Mailable;

class MailgunTransport implements TransportInterface {
     public function send(Mailable $mail) : bool {
         // Call Mailgun API
         return true;
     }
}