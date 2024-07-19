<?php
// Set your return content type
header("Content-Type: application/json; charset=utf-8");

// Website url to open
$url = 'http://localhost.com/course/ajax/course_a.php';

// Get that website's content
$handle = fopen($url, "r");

// If there is something, read and return
if ($handle) {
    while (!feof($handle)) {
        $buffer = fgets($handle, 4096);
        echo $buffer;
    }
    fclose($handle);
}
?>