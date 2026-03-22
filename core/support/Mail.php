<?php

namespace SaQle\Core\Support;

use SaQle\Core\Notifications\Mail\Mailable;
use SaQle\Core\Notifications\Mail\Jobs\SendMailJob;
use SaQle\Core\Queue\Manager\QueueManager;

class Mail {

     public static function send(Mailable $mail) : bool {
         return $mail->send();
     }

     public static function queue(Mailable $mail, int $delay = 0, int $priority = 0){
         
         $queue = new QueueManager();

         $job = new SendMailJob($mail);

         $queue->dispatch($job, config('queue.email', 'emails'), $priority, $delay);

     }

     public static function later(Mailable $mail, int $delay, int $priority = 0){
         self::queue($mail, $delay, $priority);
     }

}