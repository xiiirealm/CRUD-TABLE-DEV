<?php
session_start();
require 'db.php';
$email = $phone = '';
$error = '';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if ($email === '' || $phone === '') {
        $error = 'Email and phone are required.';
    } else {
        $stmt = $conn->prepare('SELECT id, name FROM users WHERE email = ? AND phone = ? AND role = "admin"');
        $stmt->bind_param('ss', $email, $phone);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $name);
            $stmt->fetch();
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $id;
            $_SESSION['admin_name'] = $name;
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid credentials or not an admin.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Admin Login</h1>
    <?php if ($error): ?>
        <div class="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        <label>Phone:</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required pattern="[0-9]{10,15}">
        <button type="submit" class="btn">Login</button>
    </form>
</div>
</body>
</html> 