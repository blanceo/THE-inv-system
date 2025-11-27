<?php
header('Content-Type: application/json');
include('../config/db_connect.php');

$result = $conn->query("SELECT * FROM inventory ORDER BY room, category, item");
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
