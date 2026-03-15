<?php
session_start();
require '../db/config.php';

/* ACCESS CONTROL */
if (!isset($_SESSION['username']) ||
   ($_SESSION['category'] !== 'Librarian' &&
    $_SESSION['category'] !== 'Library_Assistant')) {

    header("Location: ../login.html");
    exit();
}

$action = $_POST['action'];

if ($action === "add") {

$title = $_POST['title'];
$author = $_POST['author'];
$category = $_POST['category'];
$quantity = $_POST['quantity'];

$stmt = $conn->prepare(
"INSERT INTO books (title, author, category, quantity)
VALUES (?, ?, ?, ?)"
);

$stmt->bind_param("sssi", $title, $author, $category, $quantity);
$stmt->execute();

}

elseif ($action === "edit") {

$id = $_POST['id'];
$title = $_POST['title'];
$author = $_POST['author'];
$category = $_POST['category'];
$quantity = $_POST['quantity'];

$stmt = $conn->prepare(
"UPDATE books
SET title=?, author=?, category=?, quantity=?
WHERE id=?"
);

$stmt->bind_param("sssii", $title, $author, $category, $quantity, $id);
$stmt->execute();

}

header("Location: librarian_dashboard.php");
exit();
?>