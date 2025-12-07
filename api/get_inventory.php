<?php
header('Content-Type: application/json');
include('../config/db_connect.php');

$result = $conn->query("SELECT * FROM inventory ORDER BY room, category, item");
$data = [];
$sql = "SELECT 
    id,
    item,
    room,
    description,
    beginning,
    acquisition,
    ending,
    pullout,
    remarks,
    image_path,  -- ADD THIS LINE
    category
FROM inventory
ORDER BY item ASC";


while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
