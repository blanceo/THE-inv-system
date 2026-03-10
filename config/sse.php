<?php
require_once 'config/disable_buffering.php';

// Disable all output buffering
if (ob_get_level()) {
    ob_end_clean();
}

// Set headers for SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable Nginx buffering

session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo "event: error\n";
    echo "data: Unauthorized\n\n";
    flush();
    exit;
}

require_once 'config/db_connect.php';

// Send initial connection message
echo "retry: 1000\n";
echo "event: connected\n";
echo "data: " . json_encode(['message' => 'Connected']) . "\n\n";

// Force flush all buffers
flush();

$lastCheck = microtime(true);
$lastCount = 0;

// Get initial count
$result = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'");
$row = $result->fetch_assoc();
$lastCount = $row['count'];

while (true) {
    $currentTime = microtime(true);
    
    // Check EVERY 0.5 seconds (500ms) for instant response
    if ($currentTime - $lastCheck >= 0.5) {
        // Get current pending count
        $result = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'");
        $row = $result->fetch_assoc();
        $currentCount = $row['count'];
        
        // If count increased, there's a new reservation
        if ($currentCount > $lastCount) {
            $newCount = $currentCount - $lastCount;
            
            echo "event: newReservation\n";
            echo "id: " . time() . "\n";
            echo "data: " . json_encode([
                'count' => $newCount,
                'message' => "$newCount new reservation request(s)!",
                'total' => $currentCount,
                'timestamp' => time()
            ]) . "\n\n";
            
            // Force immediate output
            flush();
        }
        
        $lastCount = $currentCount;
        $lastCheck = $currentTime;
    }
    
    // Check if connection was closed
    if (connection_aborted()) break;
    
    // Very short sleep - 100ms
    usleep(100000);
}
$conn->close();
?>