<?php 
$host = "192.168.1.12";
$user = "root";
$pass = "";
$db = "inv_system";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>