<?php
session_start();
require '../db/config.php';

if (!isset($_SESSION['username'])) {
header("Location: login.html");
exit();
}

$librarian = $_SESSION['username'];

if(isset($_POST['issue'])){

$book_id = $_POST['book_id'];
$book_title = $_POST['book_title'];
$borrower = $_POST['borrower'];
$role = $_POST['role'];
$issue_date = date("Y-m-d");

$stmt = $conn->prepare("INSERT INTO borrowed_books (book_id,book_title,borrower_username,borrower_role,issued_by,issue_date) VALUES (?,?,?,?,?,?)");
$stmt->bind_param("isssss",$book_id,$book_title,$borrower,$role,$librarian,$issue_date);
$stmt->execute();

echo "Book Issued Successfully";

}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../css/style.css">
<title>Issue Book</title>
</head>

<body>

<h2>Issue Book</h2>

<form method="POST">

Book ID<br>
<input type="number" name="book_id" required><br><br>

Book Title<br>
<input type="text" name="book_title" required><br><br>

Borrower Username<br>
<input type="text" name="borrower" required><br><br>

Borrower Role<br>
<select name="role" required>
<option value="Student">Student</option>
<option value="Teacher">Teacher</option>
</select><br><br>

<button type="submit" name="issue">Issue Book</button>

</form>

</body>
</html>