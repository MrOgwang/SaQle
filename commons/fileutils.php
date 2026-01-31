<?php
namespace SaQle\Commons;
trait FileUtils{
	 /*
	     - given a csv file, this function parses the rows and columns into data objects.
		 @param object $file: the file to parse.
		 @param array $columns: an array of string column names for parsed data.
		 @param boolean $ignore_header: whether to ignore the header columns for the file or not.
	 */
	 public function parse_csv_file($file, $columns, $ignore_header = false, $associative_column_indexes = false, $data_separator = ",", $start = 0, $stop = 1000000){
		 $parsed_data = array();
		 $file_name = $file["tmp_name"];
		 if ($file["size"] > 0){
			 $file_handle = fopen($file_name, "r");
			 $row_counter = 0;
			 $stop = $ignore_header ? $stop + 1 : $stop;
			 while (($row = fgetcsv($file_handle, 10000, $data_separator)) !== FALSE){
				 if($row_counter >= $start && $row_counter < $stop){
					 if($ignore_header == true && $row_counter == 0){
						 $row_counter = $row_counter + 1;
						 continue;
					 }else{
						 $data_object = array();
						 if($associative_column_indexes){
							 foreach($columns as $key => $value){
								 $data_value = $value - 1 <= count($row) - 1 ? $row[$value - 1] : null;
								 $data_object[$key] = $data_value;
							 }
						 }else{
							 for($c = 0; $c < count($columns); $c++){
								 $data_value = $c <= count($row) - 1 ? $row[$c] : null;
								 $data_object[$columns[$c]] = $data_value;
							 }
						 }
						 array_push($parsed_data, $data_object);
					 }
				 }
				 $row_counter = $row_counter + 1;
			 }
			 fclose($file_handle);
		 }
		 return $parsed_data;
	 }
     /*
	     - given a csv file, this function parses the rows and columns into data objects.
		 @param object $file: the file to parse.
		 @param array $columns: an array of string column names for parsed data.
		 @param boolean $ignore_header: whether to ignore the header columns for the file or not.
	 */
	 public function parse_csv_file2($file_path, $columns, $ignore_header = false, $associative_column_indexes = false, $data_separator = ",", $start = 0, $stop = 1000000){
		 $parsed_data = array();
		 $file_handle = fopen($file_path, "r");
		 $row_counter = 0;
		 $stop = $ignore_header ? $stop + 1 : $stop;
		 while (($row = fgetcsv($file_handle, 10000, $data_separator)) !== FALSE){
			 if($row_counter >= $start && $row_counter < $stop){
				 if($ignore_header == true && $row_counter == 0){
					 $row_counter = $row_counter + 1;
					 continue;
				 }else{
					 $data_object = array();
					 if($associative_column_indexes){
						 foreach($columns as $key => $value){
							 $data_value = $value - 1 <= count($row) - 1 ? $row[$value - 1] : null;
							 $data_object[$key] = $data_value;
						 }
					 }else{
						 for($c = 0; $c < count($columns); $c++){
							 $data_value = $c <= count($row) - 1 ? $row[$c] : null;
							 $data_object[$columns[$c]] = $data_value;
						 }
					 }
					 array_push($parsed_data, $data_object);
				 }
			 }
			 $row_counter = $row_counter + 1;
		 }
		 fclose($file_handle);
		 return $parsed_data;
	 }

	 public function export_csv_file(){
		 if(isset($_POST["Export"])){
			 header('Content-Type: text/csv; charset=utf-8');  
			 header('Content-Disposition: attachment; filename=data.csv');  
			 $output = fopen("php://output", "w");  
			 fputcsv($output, array('ID', 'First Name', 'Last Name', 'Email', 'Joining Date'));  
			 $query = "SELECT * from employeeinfo ORDER BY emp_id DESC";  
			 $result = mysqli_query($con, $query);  
			 while($row = mysqli_fetch_assoc($result))  {  
				 fputcsv($output, $row);  
			 }  
			 fclose($output);  
	     }  
	 }

	 public static function scandir(string $path, ?array $exts = []): array {

	    /* Fail if the directory can't be opened */
	    if (!(is_dir($path) && $dir = opendir($path))) {
	        return [];
	    }

	    /* An array to hold the results */
	    $files = [];

	    while (($file = readdir($dir)) !== false) {
	        /* Skip anything that's not a regular file */
	        if (filetype($path . '/' . $file) !== 'file') {
	            continue;
	        }
	        /* If extensions were provided and this file doesn't match, skip it */
	        if (!empty($exts) && !in_array(pathinfo($path . '/' . $file,
	                                PATHINFO_EXTENSION), $exts)) {
	            continue;
	        }
	        /* Add this file to the array */
	        $files[] = $file;
	    }
	    closedir($dir);
	    
	    return $files;
	 }

	 /**
	 * Return an array of file paths representing the contents of the target
	 * directory, ordered by date instead of by filename.
	 * 
	 * @param string $path The target directory path
	 * @param bool $reverse Whether to sort in reverse date order (oldest first)
	 * @param array $exts If set, only find files with these extensions
	 * @return array A sorted array of absolut filesystem paths
	 */
	 public static function scandir_chrono(string $path, bool $reverse = false, ?array $exts = []): array {

	    /* Fail if the directory can't be opened */
	    if (!(is_dir($path) && $dir = opendir($path))) {
	        return [];
	    }

	    /* An array to hold the results */
	    $files = [];

	    while (($file = readdir($dir)) !== false) {
	        /* Skip anything that's not a regular file */
	        if (filetype($path . '/' . $file) !== 'file') {
	            continue;
	        }
	        /* If extensions were provided and this file doesn't match, skip it */
	        if (!empty($exts) && !in_array(pathinfo($path . '/' . $file,
	                                PATHINFO_EXTENSION), $exts)) {
	            continue;
	        }
	        /* Add this file to the array with its modification time as the key */
	        $files[filemtime($path . '/' . $file)] = $file;
	    }
	    closedir($dir);

	    /* Sort and return the array */
	    $fn = $reverse ? 'krsort' : 'ksort';
	    $fn($files);
	    return $files;
	 }

     /**
      * Restore a serialized object from file.
      * @param string $filename : The name of the file from which to get serialized object.
      * @param bool   $throw_error: Whether to fail loudly or quietly
      * */
	 public static function unserialize_from_file(string $filename, bool $throw_error = false) : mixed{
	 	 if(!file_exists($filename)){
	 	 	 if($throw_error){
	 	 	 	 throw new \Exception("The file to unserialize does not exist!");
	 	 	 }

	 	 	 return false;
	 	 }

	 	 $contents = file_get_contents($filename);
	 	 if($contents === false){
	 	 	 if($throw_error){
	 	 	 	 throw new \Exception("Could not load file contents!");
	 	 	 }

	 	 	 return false;
	 	 }

	 	 $tracker = unserialize($contents);
	 	 if($tracker === false){
	 	 	 if($throw_error){
	 	 	 	 throw new \Exception("Could not unserialize file contents!");
	 	 	 }

	 	 	 return false;
	 	 }

         return $tracker;
	 }

     /**
      * Serialize an object and save the serialized object to file.
      * @param string $filename: The path and file name to save.
      * @param mixed  $object:   The object to serialize.
      * */
	 public static function serialize_to_file(string $filename, mixed $object) : bool{
	 	 $ser = serialize($object);
         return file_put_contents($filename, $ser);
	 }
}
