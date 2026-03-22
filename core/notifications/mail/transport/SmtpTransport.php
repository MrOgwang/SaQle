<?php

namespace SaQle\Core\Notifications\Mail\Transport;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use SaQle\Core\Notifications\Mail\Mailable;

class SmtpTransport implements TransportInterface {

     protected PHPMailer $mailer;

     public function __construct(){
         $this->mailer = new PHPMailer(true);
         $this->mailer->isSMTP();
         $this->mailer->Host = config('mail.host');
         $this->mailer->SMTPAuth = true;
         $this->mailer->Username = config('mail.username');
         $this->mailer->Password = config('mail.password');
         $this->mailer->SMTPSecure = config('mail.encryption');
         $this->mailer->Port = config('mail.port');
         $this->mailer->setFrom(config('mail.sender_address'), config('mail.sender_name'));
         $this->mailer->isHTML(true);
     }

     public function send(Mailable $mailable) : bool {
         try {
            foreach ($mailable->to as $t) {
                $this->mailer->addAddress($t[0], $t[1]);
            }
            foreach ($mailable->cc as $c) {
                $this->mailer->addCC($c[0], $c[1]);
            }
            foreach ($mailable->bcc as $b) {
                $this->mailer->addBCC($b[0], $b[1]);
            }
            foreach ($mailable->attachments as $a) {
                $this->mailer->addAttachment($a[0], $a[1]);
            }

            $this->mailer->Subject = $mailable->subject;
            $this->mailer->Body = $mailable->body;
            $this->mailer->AltBody = strip_tags($mailable->body);

            return $this->mailer->send();
        }catch (Exception $e){
            log_to_file($e->getMessage());
            //logger('mail')->error($e->getMessage());
            return false;
        }
     }
}