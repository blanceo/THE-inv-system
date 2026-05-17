<?php
// api/get_inventory.php
require_once '../config/db_connect.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $result = $conn->query(
        "SELECT 
            id,
            room,
            category,
            item,
            description,
            beginning,
            acquisition,
            ending,
            pullout,
            remarks,
            image_path,
            COALESCE(is_requested, 0)  AS is_requested,
            COALESCE(request_count, 0) AS request_count
         FROM inventory
         ORDER BY item ASC"
    );

    if (!$result) {
        throw new Exception($conn->error);
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        // Cast numeric flags so JS gets integers, not strings
        $row['is_requested']  = (int)$row['is_requested'];
        $row['request_count'] = (int)$row['request_count'];
        $rows[] = $row;
    }

    echo json_encode($rows);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
