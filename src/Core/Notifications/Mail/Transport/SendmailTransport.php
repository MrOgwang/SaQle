<?php

namespace SaQle\Core\Notifications\Mail\Transport;

use SaQle\Core\Support\Mailable;

class SendmailTransport implements TransportInterface {
     public function send(Mailable $mail) : bool {
         // Call Sendmail API
         return true;
     }
}