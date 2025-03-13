<?php
class RedirectResponse{
     protected $url;
     protected $status;

     public function __construct($url, $status = 302){
         $this->url = $url;
         $this->status = $status;
     }

     public function send(){
         http_response_code($this->status);
         header("Location: {$this->url}");
         exit;
     }
}
?>
