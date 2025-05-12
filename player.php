<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timer for Table ID 8</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #e9f5ff;
            font-family: Arial, sans-serif;
            color: #333;
        }
        
        .container {
            width: 300px; /* Set width for the circle */
            height: 300px; /* Set height equal to width for circle shape */
            background-color: #fff;
            border-radius: 50%; /* Make the container circular */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .time-display {
            font-size: 1.5em;
            font-weight: bold;
            color: #007BFF;
            margin: 10px 0;
        }
        
        .cost-display {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }
        
        h2 {
            font-size: 1.2em;
            margin: 0;
            padding-top: 10px; /* To avoid text overlapping the top edge */
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Table ID 8</h2>
    <?php
    $conn = new mysqli("127.0.0.1", "root", "", "billiard_hub");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $tableId = 8;
    $sql = "SELECT start_time, stop_time, cost FROM table_timers WHERE table_id = $tableId ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $startTime = new DateTime($row['start_time']);
        $stopTime = new DateTime($row['stop_time']);
        $interval = $startTime->diff($stopTime);

        echo "<div>Start Time: " . $startTime->format('Y-m-d H:i:s') . "</div>";
        echo "<div>Stop Time: " . $stopTime->format('Y-m-d H:i:s') . "</div>";
        echo "<div class='time-display'>Elapsed Time: " . $interval->format('%H:%I:%S') . "</div>";
        echo "<div class='cost-display'>Total Cost: ₱" . number_format($row['cost'], 2) . "</div>";
    } else {
        echo "<p>No timer data available for Table ID 8.</p>";
    }

    $conn->close();
    ?>
</div>

</body>
</html>
