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

$username = $_SESSION['username'];
$message = "";
$max_books = 3;

/* =========================
   ADD BOOK
========================= */
if (isset($_POST['action']) && $_POST['action'] === 'add') {

    $title = $_POST['title'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $quantity = intval($_POST['quantity']);

    $stmt = $conn->prepare("INSERT INTO books (title, author, category, quantity) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $title, $author, $category, $quantity);

    if ($stmt->execute()) {
        header("Location: ldashboard.php");
        exit();
    } else {
        die("Error: " . $stmt->error);
    }
}

/* =========================
   BORROW BOOK (LIBRARIAN ISSUES TO STUDENT)
========================= */
if (isset($_POST['borrow_book'])) {

    $book_id = intval($_POST['book_id']);
    $student = trim($_POST['student_username']);

    if ($book_id <= 0 || empty($student)) {
        die("Invalid input");
    }

    /* CHECK STUDENT LIMIT */
    $stmt_check = $conn->prepare(
        "SELECT COUNT(*) as total FROM borrowed_books 
         WHERE borrower_username=? AND status='borrowed'"
    );
    $stmt_check->bind_param("s", $student);
    $stmt_check->execute();
    $row_check = $stmt_check->get_result()->fetch_assoc();

    if ($row_check['total'] >= $max_books) {
        die("Student borrow limit reached.");
    }

    /* GET BOOK */
    $stmt = $conn->prepare("SELECT * FROM books WHERE id=?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();

    if ($book && $book['quantity'] > 0) {

        $title = $book['title'];
        $issue_date = date("Y-m-d");
        $due_date = date("Y-m-d", strtotime("+7 days"));

        /* INSERT RECORD */
        $stmt2 = $conn->prepare(
            "INSERT INTO borrowed_books
            (book_id, book_title, borrower_username, borrower_role, issued_by, issue_date, due_date, status)
            VALUES (?, ?, ?, 'Student', ?, ?, ?, 'borrowed')"
        );

        $issued_by = $username;

        $stmt2->bind_param("isssss",
            $book_id,
            $title,
            $student,
            $issued_by,
            $issue_date,
            $due_date
        );

        if (!$stmt2->execute()) {
            die("Insert Error: " . $stmt2->error);
        }

        /* 🔥 DECREASE QUANTITY */
        $stmt3 = $conn->prepare("UPDATE books SET quantity = quantity - 1 WHERE id=?");
        $stmt3->bind_param("i", $book_id);

        if (!$stmt3->execute()) {
            die("Update Error: " . $stmt3->error);
        }

        header("Location: ldashboard.php?msg=borrowed");
        exit();

    } else {
        die("Book not available.");
    }
}

/* =========================
   RETURN BOOK
========================= */
if (isset($_POST['return_book'])) {

    $borrow_id = intval($_POST['borrow_id']);

    if ($borrow_id <= 0) {
        die("Invalid Borrow ID");
    }

    $stmt = $conn->prepare("SELECT * FROM borrowed_books WHERE id=?");
    $stmt->bind_param("i", $borrow_id);
    $stmt->execute();
    $borrow = $stmt->get_result()->fetch_assoc();

    if ($borrow) {

        $book_id = intval($borrow['book_id']);
        $return_date = date("Y-m-d");

        /* UPDATE STATUS */
        $stmt2 = $conn->prepare(
            "UPDATE borrowed_books 
             SET status='returned', return_date=? 
             WHERE id=?"
        );
        $stmt2->bind_param("si", $return_date, $borrow_id);

        if (!$stmt2->execute()) {
            die("Return Update Error: " . $stmt2->error);
        }

        /* 🔥 INCREASE QUANTITY */
        $stmt3 = $conn->prepare("UPDATE books SET quantity = quantity + 1 WHERE id=?");
        $stmt3->bind_param("i", $book_id);

        if (!$stmt3->execute()) {
            die("Return Error: " . $stmt3->error);
        }

        header("Location: borrowed_book.php?msg=returned");
        exit();
    }
}
?>