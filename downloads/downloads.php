<?php
     session_start();
	 header("Pragma: public");
	 header('Expires: 0');
     header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	 header('Cache-Control: private', false);
     if(isset($_SESSION['fileToDownloadPath'])){
		 $file_path = $_SESSION['fileToDownloadPath'];
		 $abs_path = $_SERVER['DOCUMENT_ROOT'] .$file_path;
		 $file_name = isset($_SESSION['fileToDownloadName']) ? $_SESSION['fileToDownloadName'] : $file_path;
		 $file_type = filetype($abs_path);
		 $file_size = filesize($abs_path);
		 header("Content-Type: $file_type");
		 header("Content-Length:$file_size");
		 header("Content-Disposition: attachment; filename=".$file_name."");
		 set_time_limit(0);
         $file = fopen($abs_path, "rb");
		 while(!feof($file)){
			 print(fread($file, 1024*8));
			 ob_flush();
			 flush();
		 }
	 }
?>