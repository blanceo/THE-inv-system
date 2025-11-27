<?php
session_start();
include('config/db_connect.php');

header('Content-Type: application/json');

if ($_SESSION['user_type'] !== 'admin') {
    echo json_encode(['count' => 0]);
    exit;
}

// Count pending reservations
$stmt = $conn->prepare("SELECT COUNT(*) as pending_count FROM reservations WHERE status = 'pending'");
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['count' => $row['pending_count']]);
$stmt->close();
$conn->close();
?>