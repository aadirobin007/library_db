<?php
session_start();
require '../db/config.php';

/* 🔐 ACCESS CONTROL */
if (!isset($_SESSION['username']) || $_SESSION['category'] !== 'Teacher') {
    header("Location: ../login.html");
    exit();
}

$username = $_SESSION['username'];
$requested_role = $_POST['requested_role'] ?? '';

if (empty($requested_role)) {
    $_SESSION['request_message'] = "Please select a role.";
    header("Location: tdashboard.php");
    exit();
}

/* Check if pending request exists */
$stmt = $conn->prepare(
    "SELECT id FROM role_requests WHERE username=? AND status='Pending'"
);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $_SESSION['request_message'] = "You already have a pending request.";

} else {

    $stmt = $conn->prepare(
        "INSERT INTO role_requests (username, requested_role, status)
         VALUES (?, ?, 'Pending')"
    );

    $stmt->bind_param("ss", $username, $requested_role);

    if ($stmt->execute()) {
        $_SESSION['request_message'] = "Role request submitted successfully!";
    } else {
        $_SESSION['request_message'] = "Error submitting request.";
    }

}

$stmt->close();
$conn->close();

/* Redirect back */
header("Location: tdashboard.php");
exit();
?>