<?php

namespace SaQle\Http\Response\Types;

use SaQle\Http\Response\Response;
use RuntimeException;

final class FileResponse extends Response {

     public function __construct(
         protected array $file_info, 
         int $status = 200,
         array $headers = []
     ){
         parent::__construct($status, $headers);
     }

     protected function send_content() : void {

         $path = $this->file_info['path'];

         if(!is_file($path)){
             throw new RuntimeException('File not found.');
         }

         $mime = $this->file_info['mime'] ?? mime_content_type($path) ?: 'application/octet-stream';
         $name = $this->file_info['name'];
         $size = $this->file_info['size'] ?? filesize($path);
         $inline = $this->file_info['inline'] ?? true;
         $cache = $this->file_info['cache'] ?? false;

         $this
         ->header('X-Content-Type-Options', 'nosniff')
         ->header('Content-Transfer-Encoding', 'binary')
         ->header('Content-Description', 'File Transfer')
         ->header('Content-Type', $mime)
         ->header('Content-Disposition',
                ($inline ? 'inline' : 'attachment').'; filename="'.addslashes($name).'"'
         )
         ->header('Content-Length', (string)$size);

         if($cache){
             $etag = md5_file($path);
             $last_modified = gmdate('D, d M Y H:i:s', filemtime($path)).' GMT';

             $this
             ->header('Cache-Control', 'public, max-age=31536000, immutable')
             ->header('Expires', gmdate('D, d M Y H:i:s', time() + 31536000).' GMT')
             ->header('ETag', $etag)
             ->header('Last-Modified', $last_modified);

             //304 Not Modified
             if(isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === "\"$etag\""){
                 $this->set_status(304);
                 exit;
             }

             if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= filemtime($path)){
                 $this->set_status(304);
                 exit;
             }
         }else{
             $this->header('Cache-Control', 'no-store, no-cache, must-revalidate');
         }

         $this->send_headers();

         if(ob_get_level()){
             ob_end_clean();
         }

         readfile($path);
     }
}
