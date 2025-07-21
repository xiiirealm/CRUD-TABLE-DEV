<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>User Management</h1>
    <a href="add.php" class="btn">Add User</a>
    <a href="logout.php" class="btn" style="float:right;">Logout</a>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        require 'db.php';
        $sql = "SELECT id, name, email, phone, role, created_at FROM users ORDER BY id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo '<td><a href="edit.php?id=' . $row['id'] . '" class="btn">Edit</a> ';
                echo '<form action="delete.php" method="post" style="display:inline;">
                        <input type="hidden" name="id" value="' . $row['id'] . '">
                        <button type="submit" class="btn" onclick="return confirm(\'Are you sure?\')">Delete</button>
                      </form></td>';
                echo "</tr>";
            }
        } else {
            echo '<tr><td colspan="7">No users found.</td></tr>';
        }
        $stmt->close();
        ?>
        </tbody>
    </table>
</div>
</body>
</html> 