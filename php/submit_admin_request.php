<?php
session_start();
require '../db/config.php';

if (!isset($_SESSION['username']) || $_SESSION['category'] !== 'Teacher') {
    header("Location: login.html");
    exit();
}

$username = $_SESSION['username'];

// Check if a pending request already exists
$stmt = $conn->prepare("SELECT * FROM role_requests WHERE username=? AND status='pending'");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['request_message'] = "You already have a pending request.";
} else {
    $stmt = $conn->prepare("INSERT INTO role_requests (username, requested_role) VALUES (?, 'admin')");
    $stmt->bind_param("s", $username);
    if ($stmt->execute()) {
        $_SESSION['request_message'] = "Admin request submitted successfully!";
    } else {
        $_SESSION['request_message'] = "Error submitting request.";
    }
    $stmt->close();
}

$conn->close();

// Redirect back to teacher dashboard
header("Location: tdashboard.php");
exit();
?>