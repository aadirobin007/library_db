<?php
session_start();
require '../db/config.php';
require 'validation.php';

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

    // Validate username
    if (!validate_username($username)) {
        $_SESSION['error'] = "Invalid username. Use 3-20 alphanumeric characters.";
        header("Location: admin_dashboard.php");
        exit();
    }

    // Validate password
    if (!validate_password($password)) {
        $_SESSION['error'] = "Password must be at least 6 characters.";
        header("Location: admin_dashboard.php");
        exit();
    }

    // Validate category
    $allowed_categories = ['Student', 'Teacher', 'Teacher_Admin', 'Librarian', 'Library_Assistant', 'admin'];
    if (!in_array($category, $allowed_categories)) {
        $_SESSION['error'] = "Invalid category.";
        header("Location: admin_dashboard.php");
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO users (username, password, category) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("sss", $username, $hashed_password, $category);

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
    $user_id = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
    $category = $_POST['category'];

    if (!$user_id || $user_id <= 0) {
        $_SESSION['error'] = "Invalid user ID.";
        header("Location: admin_dashboard.php");
        exit();
    }

    // Validate category
    $allowed_categories = ['Student', 'Teacher', 'Teacher_Admin', 'Librarian', 'Library_Assistant', 'admin'];
    if (!in_array($category, $allowed_categories)) {
        $_SESSION['error'] = "Invalid category.";
        header("Location: admin_dashboard.php");
        exit();
    }

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
    $user_id = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);

    if (!$user_id || $user_id <= 0) {
        $_SESSION['error'] = "Invalid user ID.";
        header("Location: admin_dashboard.php");
        exit();
    }

    // Prevent deleting own account
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $user['username'] === $_SESSION['username']) {
        $_SESSION['error'] = "Cannot delete your own account.";
        header("Location: admin_dashboard.php");
        exit();
    }

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