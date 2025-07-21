<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'user_admin'; // or whatever you named your database

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
?> 