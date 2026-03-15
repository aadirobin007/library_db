<?php
session_start();
require '../db/config.php';

/* Only Admin */
if (!isset($_SESSION['username']) || $_SESSION['category'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_dashboard.php");
    exit();
}

$action = $_POST['action'] ?? '';

/* ADD USER */
if ($action === 'add') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $category = $_POST['category'];

    $stmt = $conn->prepare(
        "INSERT INTO users (username, password, category) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("sss", $username, $password, $category);

    if ($stmt->execute()) {
        $_SESSION['success'] = "User added successfully";
    } else {
        $_SESSION['error'] = "Username already exists";
    }

    header("Location: admin_dashboard.php");
    exit();
}

/* EDIT ROLE */
if ($action === 'edit') {
    $user_id = intval($_POST['user_id']);
    $category = $_POST['category'];

    $stmt = $conn->prepare(
        "UPDATE users SET category = ? WHERE id = ?"
    );
    $stmt->bind_param("si", $category, $user_id);
    $stmt->execute();

    $_SESSION['success'] = "User role updated";
    header("Location: admin_dashboard.php");
    exit();
}

/* DELETE USER */
if ($action === 'delete') {
    $user_id = intval($_POST['user_id']);

    $stmt = $conn->prepare(
        "DELETE FROM users WHERE id = ?"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $_SESSION['success'] = "User deleted";
    header("Location: admin_dashboard.php");
    exit();
}

/* Fallback */
header("Location: admin_dashboard.php");
exit();