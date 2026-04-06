<?php

namespace SaQle\Http\Response\Types;

use SaQle\Http\Response\HttpResponse;

class FileResponse extends HttpResponse {

     protected array $file_info;
     protected int $status;

     public function __construct(array $file_info, int $status = 200){
        
         $this->file_info = $file_info;
         $this->status = $status;
     }

     public function send() : void {

         $path = $this->file_info['path'];
         $mime = $this->file_info['mime'];
         $name = $this->file_info['name'];
         $size = $this->file_info['size'];
         $inline = $this->file_info['inline'] ?? true;
         $cache = $this->file_info['cache'] ?? false;

         http_response_code($this->status);
        
         header("X-Content-Type-Options: nosniff");
         header("Content-Transfer-Encoding: binary");
         header("Content-Description: File Transfer");
         header("Content-Type: $mime");
         header("Content-Disposition: ".($inline ? 'inline' : 'attachment').';filename="'.$name.'"');
         header("Content-Length: $size");
         
         if($cache){
             $etag = md5_file($path);
             $last_modified = gmdate('D, d M Y H:i:s', filemtime($path)).' GMT';

             header("Cache-Control: public, max-age=31536000, immutable");
             header("Expires: ".gmdate('D, d M Y H:i:s', time() + 31536000)." GMT");
             header("ETag: \"$etag\"");
             header("Last-Modified: $last_modified");

             //304 Not Modified
             if(isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === "\"$etag\""){
                 http_response_code(304);
                 exit;
             }

             if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= filemtime($path)){
                 http_response_code(304);
                 exit;
             }
         }else{
             header("Cache-Control: no-store, no-cache, must-revalidate");
         }

         if(ob_get_level()){
             ob_end_clean();
         }

         readfile($path);
     }
}
