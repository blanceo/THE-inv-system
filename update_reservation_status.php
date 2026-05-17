<?php
require_once 'config/db_connect.php';
require_once 'check_session.php';

header('Content-Type: application/json');

$reservation_id = $_POST['reservation_id'] ?? null;
$status         = $_POST['status']         ?? null;

if (!$reservation_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$allowed_statuses = ['approved', 'rejected', 'cancelled', 'borrowed', 'returned'];
if (!in_array($status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Fetch the reservation so we know the item name and current status
$fetch = $conn->prepare("SELECT item_name, status FROM reservations WHERE id = ?");
$fetch->bind_param("i", $reservation_id);
$fetch->execute();
$result = $fetch->get_result();
$reservation = $result->fetch_assoc();
$fetch->close();

if (!$reservation) {
    echo json_encode(['success' => false, 'message' => 'Reservation not found']);
    exit;
}

$item_name      = $reservation['item_name'];
$current_status = $reservation['status'];

// Update reservation status
$upd = $conn->prepare("UPDATE reservations SET status = ? WHERE id = ?");
$upd->bind_param("si", $status, $reservation_id);

if (!$upd->execute()) {
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    $upd->close();
    $conn->close();
    exit;
}
$upd->close();

// ---------------------------------------------------------------
// Decrement inventory request_count when a reservation is resolved
// (approved OR rejected — the pending request is now settled)
// Only decrement if the reservation was previously 'pending'
// (avoid double-decrement if e.g. borrowed→returned)
// ---------------------------------------------------------------
$resolving_statuses = ['approved', 'rejected'];

if (in_array($status, $resolving_statuses) && $current_status === 'pending') {
    // Decrement request_count, floor at 0
    $dec = $conn->prepare(
        "UPDATE inventory
         SET request_count = GREATEST(0, request_count - 1)
         WHERE LOWER(TRIM(item)) = LOWER(TRIM(?))"
    );
    $dec->bind_param("s", $item_name);
    $dec->execute();
    $dec->close();

    // If request_count is now 0, clear is_requested flag
    $clr = $conn->prepare(
        "UPDATE inventory
         SET is_requested = 0
         WHERE LOWER(TRIM(item)) = LOWER(TRIM(?))
           AND request_count = 0"
    );
    $clr->bind_param("s", $item_name);
    $clr->execute();
    $clr->close();
}

$conn->close();

$messages = [
    'approved'  => 'Reservation approved successfully!',
    'rejected'  => 'Reservation rejected.',
    'cancelled' => 'Reservation cancelled.',
    'borrowed'  => 'Item marked as borrowed.',
    'returned'  => 'Item marked as returned.',
];

echo json_encode([
    'success' => true,
    'message' => $messages[$status] ?? 'Status updated.'
]);
?>
