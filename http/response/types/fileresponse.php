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

     public function get(string $token){
         $decoded = $this->decrypt($token, config('app.media_key'), 'media-url-salt');
         $file_data = json_decode($decoded);
         $file_name = $file_data->file_name;
         $file_path = $file_data->path;
         $abs_path = rtrim($file_path, '/\\').DIRECTORY_SEPARATOR.$file_name;
         if (file_exists($abs_path)){
             $mime = mime_content_type($abs_path);
             $is_inline = str_starts_with($mime, 'image/') || str_starts_with($mime, 'video/') || $mime === 'application/pdf';

             header('Content-Description: File Transfer');
             header('Content-Type: '.$mime);
             header('Content-Disposition: '.($is_inline ? 'inline' : 'attachment').'; filename="'.$file_name.'"');
             header('Content-Transfer-Encoding: binary');

             //cache for 1 year
             header('Cache-Control: public, max-age=31536000, immutable');
             header('Expires: '.gmdate('D, d M Y H:i:s', time() + 31536000).' GMT');
             header('Content-Length: '. filesize($abs_path));
             ob_clean();
             flush();
             readfile($abs_path);
             exit;
         }
     }
}
