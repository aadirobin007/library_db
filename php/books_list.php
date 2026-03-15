<?php
session_start();
require '../db/config.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../sdashboard.php");
    exit();
}

$username = $_SESSION['username'];

/* BORROW BOOK */
if(isset($_POST['borrow'])){

$id = $_POST['id'];

$stmt = $conn->prepare("SELECT * FROM books WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

if($book['quantity'] > 0){

$title = $book['title'];
$issue_date = date("Y-m-d");

$stmt2 = $conn->prepare("INSERT INTO borrowed_books (book_id, book_title, borrower_username, borrower_role, issued_by, issue_date, status) VALUES (?, ?, ?, 'Student', 'Self Borrow', ?, 'borrowed')");
$stmt2->bind_param("isss",$id,$title,$username,$issue_date);
$stmt2->execute();

/* Reduce quantity */

$newqty = $book['quantity'] - 1;

$stmt3 = $conn->prepare("UPDATE books SET quantity=? WHERE id=?");
$stmt3->bind_param("ii",$newqty,$book_id);
$stmt3->execute();

$message = "Book borrowed successfully!";

}else{

$message = "Book not available.";

}

}

$result = $conn->query("SELECT * FROM books");
?>

<!DOCTYPE html>
<html>
<head>

<title>Library Books</title>

<link rel="stylesheet" href="../css/style.css">

</head>

<body>

<header class="top-header">

<h1>Library Books</h1>

<div>
<a href="sdashboard.php" class="nav-btn">Dashboard</a>
<a href="student_borrowed_book.php" class="nav-btn">My Books</a>
<a href="../logout.php" class="nav-btn">Logout</a>
</div>

</header>

<div class="content">

<h2 class="center-text">Available Books</h2>

<?php
if(isset($message)){
echo "<p class='center-text'>$message</p>";
}
?>

<table>

<tr>
<th>Book ID</th>
<th>Title</th>
<th>Author</th>
<th>Category</th>
<th>Quantity</th>
<th>Borrow</th>
</tr>

<?php

if($result->num_rows > 0){

while($row = $result->fetch_assoc()){

echo "<tr>";

echo "<td>".$row['id']."</td>";
echo "<td>".$row['title']."</td>";
echo "<td>".$row['author']."</td>";
echo "<td>".$row['category']."</td>";
echo "<td>".$row['quantity']."</td>";

echo "<td>
<form method='POST'>
<input type='hidden' name='id' value='".$row['id']."'>
<button type='submit' name='borrow'>Borrow</button>
</form>
</td>";

echo "</tr>";

}

}else{

echo "<tr><td colspan='6'>No books available</td></tr>";

}

?>

</table>

</div>

</body>
</html>