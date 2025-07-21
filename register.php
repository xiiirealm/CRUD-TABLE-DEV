<?php
require 'db.php';
$name = $email = $phone = '';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Validation
    if ($name === '') $errors[] = 'Name is required.';
    if ($email === '') $errors[] = 'Email is required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
    if ($phone === '') $errors[] = 'Phone is required.';
    elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) $errors[] = 'Phone must be 10-15 digits.';

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
        $stmt = $conn->prepare('INSERT INTO users (name, email, phone, role, created_at) VALUES (?, ?, ?, "user", NOW())');
        $stmt->bind_param('sss', $name, $email, $phone);
        if ($stmt->execute()) {
            $success = 'Registration successful!';
            $name = $email = $phone = '';
        } else {
            $errors[] = 'Failed to register user.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>User Registration</h1>
    <?php if ($errors): ?>
        <div class="alert">
            <?php foreach ($errors as $error) echo '<div>' . htmlspecialchars($error) . '</div>'; ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        <label>Phone:</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required pattern="[0-9]{10,15}">
        <button type="submit" class="btn">Register</button>
    </form>
</div>
</body>
</html> 