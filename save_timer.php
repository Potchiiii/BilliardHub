// save_start_time.php
$conn = new mysqli("127.0.0.1", "root", "", "billiard_hub");

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

$tableId = $_POST['table_id'];
$startTime = $_POST['start_time'];

// Insert start time into the table_timers table
$sql = "INSERT INTO table_timers (table_id, start_time) VALUES (?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $tableId, $startTime);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Start time saved successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to save start time: " . $conn->error]);
}

$stmt->close();
$conn->close();
