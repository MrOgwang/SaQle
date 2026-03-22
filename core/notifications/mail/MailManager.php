<?php

namespace SaQle\Core\Notifications\Mail;

use SaQle\Core\Notifications\Mail\Transport\TransportInterface;
use SaQle\Core\Notifications\Mail\Transport\{
     SmtpTransport, 
     MailgunTransport, 
     SendmailTransport
 };

class MailManager {

     protected TransportInterface $transport;

     public function __construct(){
         $this->transport = $this->resolve_transport();
     }

     protected function resolve_transport() : TransportInterface {
         $driver = config('mail.driver', 'smtp');

         return match($driver){
             'smtp' => new SmtpTransport(),
             'sendmail' => new SendmailTransport(),
             'mailgun' => new MailgunTransport(),
             default => new SmtpTransport(),
         };
     }

     public function send(Mailable $mail) : bool {
         return $this->transport->send($mail);
     }
}