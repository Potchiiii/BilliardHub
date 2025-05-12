// reset_timer.php
$conn = new mysqli("127.0.0.1", "root", "", "billiard_hub");

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

$tableId = $_POST['table_id'];

// Reset the timer entry for the table
$sql = "UPDATE table_timers SET start_time = NULL, stop_time = NULL, cost = NULL WHERE table_id = ? AND stop_time IS NULL";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tableId);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Timer reset successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to reset timer: " . $conn->error]);
}

$stmt->close();
$conn->close();
