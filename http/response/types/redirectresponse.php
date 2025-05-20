<?php
namespace SaQle\Http\Response\Types;

class RedirectResponse{
     protected ?string $url = null;
     protected int $status = 302;

     public function __construct(?string $url = null, int $status = 302){
         $this->url = $url ?? $this->current_url();
         $this->status = $status;
     }

     public function current_url(){
         $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
         return $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
     }

     public function send(){
         http_response_code($this->status);
         header("Location: {$this->url}");
         exit;
     }
}

