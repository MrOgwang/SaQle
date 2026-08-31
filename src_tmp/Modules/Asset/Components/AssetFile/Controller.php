<?php

namespace SaQle\Modules\Asset\Components\AssetFile;

use SaQle\Http\Response\Message;
use SaQle\Core\Registries\ComponentRegistry;

class Controller {

     public function serve(string $asset) {

         //Separate payload from signature.
         $parts = explode('.', $asset, 2);

         if(count($parts) !== 2){
             throw new not_found_exception('Asset not found!');
         }

         [$payload, $signature] = $parts;

         //Verify signature.
         $expected_signature = hash_hmac('sha256', $payload, config('app.key'));

         if(!hash_equals($expected_signature, $signature)){
             throw new authorization_exception("Access denied!");
         }

         //Decode payload.
         $json = base64_decode(url_to_base64($payload));

         if($json === false){
             throw new not_found_exception("Asset not found!");
         }

         $data = json_decode($json, true);

         if(!is_array($data) || !isset($data['component'], $data['path'])){
             throw new not_found_exception("Asset not found!");
         }

         $component = $data['component'];
         $path      = $data['path'];

         //Resolve component.
         $component_def = ComponentRegistry::get_definition($component);

         $component_path = $component_def->path;

         //Resolve the actual file.
         $file = realpath(path_join([$component_path, 'Assets', $path]));

         //File must exist and be a regular readable file.
         if($file === false || !is_file($file) || !is_readable($file)){
             throw new not_found_exception("Asset not found!");
         }

         /*
         * VERY IMPORTANT:
         *
         * Make sure the resolved file is still inside
         * the component directory.
         */
         if(!str_starts_with($file, $component_path . DIRECTORY_SEPARATOR)){
             throw new authorization_exception("Access denied!");
         }

         //Determine MIME type.
         $mime = mime_content_type($file);
         if(!$mime){
             $mime = 'application/octet-stream';
         }

         $is_inline = str_starts_with($mime, 'image/') || str_starts_with($mime, 'video/') || $mime === 'application/pdf';

         $asset = [
             'mime' => $mime,
             'inline' => $is_inline,
             'name' => basename($file),
             'path' => $file,
             'cache' => true,
             'size' => filesize($file)
         ];
         
         return Message::file($asset);
     }
}