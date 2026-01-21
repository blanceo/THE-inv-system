<?php 
//my phone data ip address, should be changed when use of hotspot
// $host = "localhost"; WITHOUT DATA!!
// $host = "192.168.63.170"; PHONE DATA!!
$host = "localhost"; 
$user = "root";
$pass = "";
$db = "inv_system";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>