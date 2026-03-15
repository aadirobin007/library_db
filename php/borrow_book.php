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

$book_id = $_POST['book_id'];
$username = $_POST['username'];
$due_date = $_POST['due_date'];

/* CHECK BOOK QUANTITY */

$stmt = $conn->prepare("SELECT quantity FROM books WHERE id=?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['quantity'] <= 0) {

    header("Location: librarian_dashboard.php");
    exit();

}

/* INSERT BORROW RECORD */

$stmt = $conn->prepare(
"INSERT INTO borrowed_books (book_id, username, due_date)
VALUES (?, ?, ?)"
);

$stmt->bind_param("iss", $book_id, $username, $due_date);
$stmt->execute();

/* REDUCE BOOK QUANTITY */

$stmt = $conn->prepare(
"UPDATE books SET quantity = quantity - 1 WHERE id=?"
);

$stmt->bind_param("i", $book_id);
$stmt->execute();

header("Location: librarian_dashboard.php");
exit();
?>