
<?php
ini_set('display_errors', 1);

ini_set('log_errors', 1);

ini_set('error_log', dirname(__FILE__) . '/error_log.txt');  

error_reporting(E_ALL);

// require("constants.php"); Раньше константы держал в отдельном файле



$host="localhost";
$username = "username";
$password = "password";
$database = "database";

	
$db = mysqli_connect($host,$username,$password,$database);
	
?>