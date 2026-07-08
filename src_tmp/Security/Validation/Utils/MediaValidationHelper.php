<?php
namespace SaQle\Security\Validation\Utils;

trait MediaValidationHelper {
     private function get_video_meta_data(string $path): ? array {
         $cmd = "ffprobe -v error -select_streams v:0 -show_entries stream=width,height,duration -of json ".escapeshellarg($path);
         $output = shell_exec($cmd);
         if(!$output) return null;

         $data = json_decode($output, true);

         if(!isset($data['streams'][0])) return null;

         return $data['streams'][0];
     }
}
