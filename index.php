<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "billiard_hub";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle add table form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_table'])) {
    $name = $_POST['name'];
    $cost_per_hour = $_POST['cost_per_hour'];

    $stmt = $conn->prepare("INSERT INTO billiard_tables (name, cost_per_hour) VALUES (?, ?)");
    $stmt->bind_param("sd", $name, $cost_per_hour);
    $stmt->execute();
    $stmt->close();
}

// Handle edit request form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_table'])) {
    $id = $_POST['table_id'];
    $name = $_POST['name'];
    $cost_per_hour = $_POST['cost_per_hour'];

    $stmt = $conn->prepare("UPDATE billiard_tables SET name = ?, cost_per_hour = ? WHERE id = ?");
    $stmt->bind_param("sdi", $name, $cost_per_hour, $id);
    $stmt->execute();
    $stmt->close();
}

// Handle delete request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_table'])) {
    $id = $_POST['table_id'];

    $stmt = $conn->prepare("DELETE FROM billiard_tables WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Fetch all tables
$result = $conn->query("SELECT * FROM billiard_tables");

// Check if edit button was clicked and get table details
$editMode = false;
$editTable = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_button'])) {
    $editMode = true;
    $editId = $_POST['table_id'];
    $editTable = $conn->query("SELECT * FROM billiard_tables WHERE id = $editId")->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <h1>Admin Dashboard - Manage Tables</h1>

    <!-- Add Table Form -->
    <h2>Add Table</h2>
    <form method="POST" action="">
        <input type="text" name="name" placeholder="Table Name" required>
        <input type="number" name="cost_per_hour" placeholder="Cost per Hour" step="0.01" required>
        <button type="submit" name="add_table">Add Table</button>
    </form>

    <!-- Edit Table Form -->
    <?php if ($editMode && $editTable): ?>
        <h2>Edit Table</h2>
        <form method="POST" action="">
            <input type="hidden" name="table_id" value="<?php echo $editTable['id']; ?>">
            <input type="text" name="name" value="<?php echo htmlspecialchars($editTable['name']); ?>" required>
            <input type="number" name="cost_per_hour" value="<?php echo htmlspecialchars($editTable['cost_per_hour']); ?>" step="0.01" required>
            <button type="submit" name="edit_table" class="edit-button">Save Changes</button>
            <button type="button" onclick="window.location.href = window.location.href;">Cancel</button>
        </form>
    <?php endif; ?>

    <!-- Existing Tables -->
    <h2>Existing Tables</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Cost per Hour</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo number_format($row['cost_per_hour'], 2); ?></td>
                        <td style="display: flex; justify-content: flex-start; gap: 10px;">
                            <form method="POST" action="" class="inline-form" style="display:inline;">
                                <input type="hidden" name="table_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="edit_button" class="edit-button">Edit</button>
                            </form>
                            <form method="POST" action="" class="inline-form" style="display:inline;">
                                <input type="hidden" name="table_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_table" class="delete-button" onclick="return confirm('Are you sure you want to delete this table?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No tables available</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
