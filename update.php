<?php
require 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? '';
    $errors = [];

    if (!$id || !is_numeric($id)) $errors[] = 'Invalid user ID.';
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

    if (!$errors) {
        $stmt = $conn->prepare('UPDATE users SET name=?, email=?, phone=?, role=? WHERE id=?');
        $stmt->bind_param('ssssi', $name, $email, $phone, $role, $id);
        $stmt->execute();
        $stmt->close();
        header('Location: index.php');
        exit;
    } else {
        // Optionally, you can redirect back to edit.php?id=... with error messages
        header('Location: edit.php?id=' . urlencode($id) . '&error=' . urlencode(implode(", ", $errors)));
        exit;
    }
} else {
    header('Location: index.php');
    exit;
} 