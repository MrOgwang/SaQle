<?php
function compress_output($p_output){
	 if(strlen($p_output) >= 1000){
         $gzip = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');
         $deflate = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate');
         $encoding = (($gzip) ? 'gzip' : (($deflate) ? 'deflate' : 'none'));
         if(!strstr($_SERVER['HTTP_USER_AGENT'], 'Opera') && preg_match('/^Mozilla\/4\.0 \(compatible; MSIE ([0-9]\.[0-9])/i', $_SERVER['HTTP_USER_AGENT'], $matches)){
			 $version = floatval($matches[1]);
             if($version < 6) $encoding = 'none';
             if($version == 6 && !strstr($_SERVER['HTTP_USER_AGENT'], 'EV1')) $encoding = 'none';
         }
         if($encoding != 'none'){
			 header('Content-Encoding: '.$encoding);
			 $p_output = gzencode($p_output, 6, (($gzip) ? FORCE_GZIP : FORCE_DEFLATE));
			 header('Content-Length: '.strlen($p_output));
		 }
     }
	 return ($p_output);
}
ob_start('compress_output');
