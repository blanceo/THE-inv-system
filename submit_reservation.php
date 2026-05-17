<?php
require_once 'config/db_connect.php';
require_once 'check_session.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$teacher_id = $_SESSION['user_id'];

// Get form data
$item_names_json        = $_POST['item_names']         ?? '';
$date_needed            = $_POST['date_needed']         ?? '';
$time_needed            = $_POST['time_needed']         ?? NULL;
$purpose                = $_POST['purpose']             ?? '';
// NEW: array of booleans indicating which items came from autocomplete
$from_autocomplete_json = $_POST['from_autocomplete']  ?? '[]';

// Validate inputs
if (empty($item_names_json) || empty($date_needed)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$item_names       = json_decode($item_names_json, true);
$from_autocomplete = json_decode($from_autocomplete_json, true);

if (!is_array($item_names) || count($item_names) === 0) {
    echo json_encode(['success' => false, 'message' => 'No items specified']);
    exit;
}

if (!is_array($from_autocomplete)) {
    $from_autocomplete = [];
}

$success_count = 0;
$failed_count  = 0;

foreach ($item_names as $index => $item_name) {
    $item_name = trim($item_name);
    if (empty($item_name)) continue;

    // Insert reservation
    $stmt = $conn->prepare(
        "INSERT INTO reservations (teacher_id, item_name, date_needed, time_needed, purpose, status, created_at)
         VALUES (?, ?, ?, ?, ?, 'pending', NOW())"
    );
    $stmt->bind_param("issss", $teacher_id, $item_name, $date_needed, $time_needed, $purpose);

    if ($stmt->execute()) {
        $success_count++;

        // Only flag inventory rows for items selected via autocomplete (exact match)
        $was_autocomplete = isset($from_autocomplete[$index]) ? (bool)$from_autocomplete[$index] : false;

        if ($was_autocomplete) {
            // Exact-match lookup — increment request_count and set is_requested = 1
            $upd = $conn->prepare(
                "UPDATE inventory
                 SET request_count = request_count + 1,
                     is_requested  = 1
                 WHERE LOWER(TRIM(item)) = LOWER(TRIM(?))"
            );
            $upd->bind_param("s", $item_name);
            $upd->execute();
            $upd->close();
        }
    } else {
        $failed_count++;
    }

    $stmt->close();
}

$conn->close();

if ($success_count > 0) {
    $message = $success_count === 1
        ? "Reservation request submitted successfully!"
        : "$success_count reservation requests submitted successfully!";

    if ($failed_count > 0) {
        $message .= " ($failed_count failed)";
    }

    echo json_encode(['success' => true, 'message' => $message]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit reservations']);
}
?>
