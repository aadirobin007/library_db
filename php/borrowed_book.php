<?php
session_start();
require '../db/config.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../login.html");
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['category'];

/* STUDENT VIEW */

if ($role === "Student") {

$stmt = $conn->prepare(
"SELECT borrowed_books.id,
books.title,
borrowed_books.borrow_date,
borrowed_books.due_date

FROM borrowed_books

JOIN books ON books.id = borrowed_books.book_id

WHERE borrowed_books.username=? 
AND borrowed_books.status='Borrowed'"
);

$stmt->bind_param("s", $username);

}

/* TEACHER / LIBRARIAN VIEW */

else {

$stmt = $conn->prepare(
"SELECT borrowed_books.id,
borrowed_books.borrower_username,
books.title,
borrowed_books.issue_date,
borrowed_books.due_date

FROM borrowed_books

JOIN books ON books.id = borrowed_books.book_id

WHERE borrowed_books.status='Borrowed'"
);

}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>

<title>Borrowed Books</title>
<link rel="stylesheet" href="../css/style.css">

</head>

<body>

<h2>Borrowed Books</h2>

<table border="1">

<tr>

<?php if($role !== "Student") echo "<th>User</th>"; ?>

<th>Book</th>
<th>Borrow Date</th>
<th>Due Date</th>

<?php
if($role === "Librarian" || $role === "Library_Assistant"){
echo "<th>Action</th>";
}
?>

</tr>

<?php while($row = $result->fetch_assoc()): ?>

<tr>

<?php if($role !== "Student") echo "<td>".$row['borrower_username']."</td>"; ?>

<td><?php echo htmlspecialchars($row['title']); ?></td>

<td><?php echo $row['issue_date']; ?></td>

<td><?php echo $row['due_date']; ?></td>

<?php if($role === "Librarian" || $role === "Library_Assistant"): ?>

<td>

<form method="POST" action="return_book.php">

<input type="hidden" name="borrow_id"
value="<?php echo $row['id']; ?>">

<button type="submit">Return</button>

</form>

</td>

<?php endif; ?>

</tr>

<?php endwhile; ?>

</table>

</body>
</html>