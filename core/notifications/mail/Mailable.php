<?php

namespace SaQle\Core\Notifications\Mail;

use Exception;
use SaQle\Core\Notifications\Mail\Events\{
     MailSending, 
     MailSent, 
     MailFailed
};

abstract class Mailable {
     public array $to = [];
     public array $cc = [];
     public array $bcc = [];
     public array $attachments = [];
     public string $subject = '';
     public string $body = '';
     public bool $is_html = true;

     abstract public function build() : void;

     public function send() : bool {

         $this->build();

         event(new MailSending($this));

         $success = (new MailManager())->send($this);

         if($success){
             event(new MailSent($this));
         }else{
             event(new MailFailed($this));
         }

         return $success;
     }

     public function to(string $email, string $name = '') : self {
         $this->to[] = [$email, $name];
         return $this;
     }

     public function cc(string $email, string $name = '') : self {
         $this->cc[] = [$email, $name];
         return $this;
     }

     public function bcc(string $email, string $name = '') : self {
         $this->bcc[] = [$email, $name];
         return $this;
     }

     public function subject(string $subject) : self {
         $this->subject = $subject;
         return $this;
     }

     public function view(string $path, array $data = []) : self {
         $this->body = $this->render_view($path, $data);
         return $this;
     }

     protected function render_view(string $path, array $data) : string {
         $full_path = path_join([config('base_path'), $path]);

         if(!file_exists($full_path)){
             throw new Exception("Mail template not found: {$full_path}");
         }

         $html = file_get_contents($full_path);

         foreach ($data as $key => $value) {
             $html = str_replace('{{ '.$key.' }}', $value, $html);
             $html = str_replace('{{'.$key.'}}', $value, $html);
         }

         return $html;
     }

     public function attach(string $path, string $name = '') : self {
         $this->attachments[] = [$path, $name];
         return $this;
     }
}