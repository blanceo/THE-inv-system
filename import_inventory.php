<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('config/db_connect.php');

$json = file_get_contents("Lab_inventory_masterlist.json");
$data = json_decode($json, true);

if (!is_array($data)) {
    die("Failed to read JSON.");
}

$stmt = $conn->prepare("INSERT INTO inventory
  (room, category, item, description, beginning, acquisition, ending, pullout, remarks)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($data as $row) {
    $stmt->bind_param(
        "sssssssss",
        $row['Room'],
        $row['Category'],
        $row['Item'],
        $row['Description'],
        $row['Beginning'],
        $row['Acquisition'],
        $row['Ending'],
        $row['PullOut'],
        $row['Remarks']
    );
    $stmt->execute();
}

echo "Imported successfully!";
