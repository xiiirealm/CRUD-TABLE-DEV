<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<?php
require 'db.php';
$name = $email = $phone = $role = '';
$errors = [];
$success = false;

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

    // Check for unique email
    if (!$errors) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email already exists.';
        }
        $stmt->close();
    }

    // Insert if no errors
    if (!$errors) {
        $stmt = $conn->prepare('INSERT INTO users (name, email, phone, role, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->bind_param('ssss', $name, $email, $phone, $role);
        if ($stmt->execute()) {
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Failed to add user.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Add User</h1>
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
        <button type="submit" class="btn">Add User</button>
    </form>
</div>
</body>
</html> 