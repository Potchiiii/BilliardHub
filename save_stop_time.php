// save_stop_time.php
$conn = new mysqli("127.0.0.1", "root", "", "billiard_hub");

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

$tableId = $_POST['table_id'];
$stopTime = $_POST['stop_time'];
$cost = $_POST['cost'];

// Update the stop time and cost for the session
$sql = "UPDATE table_timers SET stop_time = ?, cost = ? WHERE table_id = ? AND stop_time IS NULL";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sdi", $stopTime, $cost, $tableId);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(["status" => "success", "message" => "Stop time and cost saved successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to save stop time or cost: " . $conn->error]);
}

$stmt->close();
$conn->close();
