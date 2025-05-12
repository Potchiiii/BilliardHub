<?php
// db_connect.php
$servername = "localhost";
$username   = "root";       // default username for XAMPP
$password   = "";           // default password for XAMPP
$dbname     = "timer_db";     // ensure this database exists

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
