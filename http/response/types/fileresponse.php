<?php
class FileResponse{
     protected $filePath;
     protected $fileName;
     protected $status;

     public function __construct($filePath, $fileName, $status = 200){
         $this->filePath = $filePath;
         $this->fileName = $fileName;
         $this->status = $status;
     }

     public function send(){
         if(!file_exists($this->filePath)) {
             throw new Exception("File not found");
         }

         http_response_code($this->status);
         header("Content-Type: application/octet-stream");
         header("Content-Disposition: attachment; filename={$this->fileName}");
         readfile($this->filePath);
     }
}
