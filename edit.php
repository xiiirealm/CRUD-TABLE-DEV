<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<?php
require 'db.php';
$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: index.php');
    exit;
}

$name = $email = $phone = $role = '';
$errors = [];

// Load user data
$stmt = $conn->prepare('SELECT name, email, phone, role FROM users WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($name, $email, $phone, $role);
if (!$stmt->fetch()) {
    $stmt->close();
    header('Location: index.php');
    exit;
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? '';

    // Validation
    if ($name === '') $errors[] = 'Name is required.';
    if ($email === '') $errors[] = 'Email is required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
    if ($phone === '') $errors[] = 'Phone is required.';
    elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) $errors[] = 'Phone must be 10-15 digits.';
    if ($role !== 'admin' && $role !== 'user') $errors[] = 'Role must be admin or user.';

    // Check for unique email (exclude current user)
    if (!$errors) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->bind_param('si', $email, $id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email already exists.';
        }
        $stmt->close();
    }

    // Update if no errors
    if (!$errors) {
        $stmt = $conn->prepare('UPDATE users SET name=?, email=?, phone=?, role=? WHERE id=?');
        $stmt->bind_param('ssssi', $name, $email, $phone, $role, $id);
        if ($stmt->execute()) {
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Failed to update user.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Edit User</h1>
    <a href="index.php" class="btn">Back to Dashboard</a>
    <?php if ($errors): ?>
        <div class="alert">
            <?php foreach ($errors as $error) echo '<div>' . htmlspecialchars($error) . '</div>'; ?>
        </div>
    <?php endif; ?>
    <form method="post" action="">
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        <label>Phone:</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required pattern="[0-9]{10,15}">
        <label>Role:</label>
        <select name="role" required>
            <option value="">Select role</option>
            <option value="admin" <?php if($role==='admin') echo 'selected'; ?>>Admin</option>
            <option value="user" <?php if($role==='user') echo 'selected'; ?>>User</option>
        </select>
        <button type="submit" class="btn">Update User</button>
    </form>
</div>
</body>
</html> 