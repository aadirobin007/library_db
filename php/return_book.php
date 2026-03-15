<?php
session_start();
require '../db/config.php';

if (!isset($_SESSION['username']) ||
   ($_SESSION['category'] !== 'Librarian' &&
    $_SESSION['category'] !== 'Library_Assistant')) {

    header("Location: ../login.html");
    exit();
}

$borrow_id = $_POST['borrow_id'];

/* GET BOOK ID */

$stmt = $conn->prepare(
"SELECT book_id FROM borrowed_books WHERE id=?"
);

$stmt->bind_param("i", $borrow_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$book_id = $row['book_id'];

/* UPDATE STATUS */

$stmt = $conn->prepare(
"UPDATE borrowed_books SET status='Returned' WHERE id=?"
);

$stmt->bind_param("i", $borrow_id);
$stmt->execute();

/* INCREASE QUANTITY */

$stmt = $conn->prepare(
"UPDATE books SET quantity = quantity + 1 WHERE id=?"
);

$stmt->bind_param("i", $book_id);
$stmt->execute();

header("Location: borrowed_books.php");
exit();
?>