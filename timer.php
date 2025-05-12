<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billiard Hub Timers</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #e9f5ff; /* Pale blue */
            margin: 0;
            padding: 20px;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #333;
            font-weight: 300;
            margin-bottom: 40px;
        }

        #tables {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px; /* Space between tables */
            max-width: 100vw; /* Ensure tables fit within the viewport */
            overflow: hidden; /* Prevent scrollbars */
        }

        .table {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            flex: 1 1 calc(25% - 20px); /* Adjust the width */
            max-width: 300px; /* Set a maximum width */
            min-width: 200px; /* Set a minimum width */
            margin: 0 auto 20px; /* Center cards in the container */
        }

        .table.active {
            background-color: #b2ebf2; /* Soft teal background */
            border: 1px solid #80deea; /* Slightly darker teal border */
        }

        .table h3 {
            margin-top: 0;
            font-size: 1.5em;
            font-weight: 500;
            color: #333;
        }

        .table div {
            margin-bottom: 10px;
            font-size: 1.1em;
            color: #555;
        }

        .btn {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            margin-right: 10px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .btn-reset {
            background-color: #ff4c4c;
        }

        .btn-reset:hover {
            background-color: #d13d3d;
        }

        .cost {
            font-weight: bold;
            font-size: 1.2em;
            color: #333;
        }

        .time-display {
            font-size: 1.8em;
            font-weight: bold;
            color: #007BFF;
        }
    </style>
</head>
<body>

<h1>Billiard Hub - Table Timers</h1>

<div id="tables">
    <?php
    // Connect to database
    $conn = new mysqli("127.0.0.1", "root", "", "billiard_hub");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch tables from the database
    $sql = "SELECT * FROM billiard_tables";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Output data for each table
        while ($row = $result->fetch_assoc()) {
            $tableId = $row['id'];
            $tableName = htmlspecialchars($row['name']);
            $costPerHour = htmlspecialchars($row['cost_per_hour']);
            echo <<<HTML
                <div class="table" id="table{$tableId}">
                    <h3>{$tableName}</h3>
                    <div>Time: <span class="time-display" id="time{$tableId}">00:00:00</span></div>
                    <div>Cost: ₱<span class="cost" id="cost{$tableId}">0.00</span></div>
                    <div>Cost per Hour: ₱<span class="cost-per-hour" id="costPerHour{$tableId}">{$costPerHour}</span></div>
                    <button class="btn" onclick="startTimer({$tableId}, {$costPerHour})">Start</button>
                    <button class="btn" onclick="stopTimer({$tableId})">Stop</button>
                    <button class="btn btn-reset" onclick="resetTimer({$tableId})">Reset</button>
                </div>
            HTML;
        }
    } else {
        echo "<p>No tables available.</p>";
    }

    $conn->close();
    ?>
</div>

<script>
    let timers = {};
    let intervals = {};

    function startTimer(tableId, rate) {
        if (!timers[tableId]) {
            timers[tableId] = {
                startTime: new Date(),
                elapsedTime: 0,
                rate: rate
            };
        } else {
            timers[tableId].startTime = new Date() - timers[tableId].elapsedTime;
        }

        document.getElementById(`table${tableId}`).classList.add('active');

        intervals[tableId] = setInterval(() => {
            const now = new Date();
            timers[tableId].elapsedTime = now - timers[tableId].startTime;
            updateDisplay(tableId);
        }, 1000);
    }

    function stopTimer(tableId) {
        clearInterval(intervals[tableId]);
        document.getElementById(`table${tableId}`).classList.remove('active');

        // Calculate the cost and end time
        const stopTime = new Date();
        const startTime = timers[tableId].startTime;
        const cost = (timers[tableId].elapsedTime / (1000 * 60 * 60)) * timers[tableId].rate;

        // Format start and stop times to MySQL datetime format (YYYY-MM-DD HH:MM:SS)
        const formattedStartTime = formatDateTime(startTime);
        const formattedStopTime = formatDateTime(stopTime);

        // Save the timer data to the database
        saveTimerData(tableId, formattedStartTime, formattedStopTime, cost);
    }

    function resetTimer(tableId) {
    clearInterval(intervals[tableId]);
    timers[tableId] = null;
    document.getElementById(`table${tableId}`).classList.remove('active');
    updateDisplay(tableId, true);

    // Reset timer data in the database
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "reset_timer.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.status === "success") {
                console.log("Timer data reset successfully.");
            } else {
                console.error("Failed to reset timer data:", response.message);
            }
        }
    };

    xhr.send(`table_id=${tableId}`);
}


    function updateDisplay(tableId, reset = false) {
        const timeDisplay = document.getElementById(`time${tableId}`);
        const costDisplay = document.getElementById(`cost${tableId}`);

        if (reset) {
            timeDisplay.textContent = "00:00:00";
            costDisplay.textContent = "0.00";
            return;
        }

        const elapsedTime = timers[tableId].elapsedTime;
        const hours = Math.floor(elapsedTime / (1000 * 60 * 60));
        const minutes = Math.floor((elapsedTime % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((elapsedTime % (1000 * 60)) / 1000);

        timeDisplay.textContent = `${padZero(hours)}:${padZero(minutes)}:${padZero(seconds)}`;

        const cost = (elapsedTime / (1000 * 60 * 60)) * timers[tableId].rate;
        costDisplay.textContent = cost.toFixed(2);
    }

    function padZero(number) {
        return number < 10 ? '0' + number : number;
    }

    function formatDateTime(date) {
        const year = date.getFullYear();
        const month = padZero(date.getMonth() + 1);
        const day = padZero(date.getDate());
        const hours = padZero(date.getHours());
        const minutes = padZero(date.getMinutes());
        const seconds = padZero(date.getSeconds());
        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    }

    function saveTimerData(tableId, startTime, stopTime, cost) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "save_timer.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.status === "success") {
                    console.log("Timer data saved successfully.");
                } else {
                    console.error("Failed to save timer data:", response.message);
                }
            }
        };

        xhr.send(`table_id=${tableId}&start_time=${startTime}&stop_time=${stopTime}&cost=${cost}`);
    }
</script>
</body>
</html>
