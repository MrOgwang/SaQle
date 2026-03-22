<?php

namespace SaQle\Core\Notifications\Mail\Jobs;

use SaQle\Core\Queue\Jobs\Job;
use SaQle\Core\Notifications\Mail\Mailable;
use SaQle\Core\Support\Mail;

class SendMailJob extends Job {

     protected Mailable $mail;

     public function __construct(Mailable $mail){
         $this->mail = $mail;
         parent::__construct();
     }

     public function handle(){
         return Mail::send($this->mail);
     }
}