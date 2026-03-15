<?php
session_start();
require '../db/config.php';

if (!isset($_SESSION['username'])) {
header("Location: login.html");
exit();
}

$result = $conn->query("SELECT * FROM borrowed_books");
?>

<!DOCTYPE html>
<html>
<head>
<title>All Borrowed Books</title>
</head>

<body>

<h2>Borrowed Books</h2>

<table border="1">

<tr>
<th>Book ID</th>
<th>Title</th>
<th>Borrower</th>
<th>Role</th>
<th>Issued By</th>
<th>Issue Date</th>
<th>Status</th>
</tr>

<?php while($row = $result->fetch_assoc()){ ?>

<tr>

<td><?php echo $row['book_id']; ?></td>
<td><?php echo $row['book_title']; ?></td>
<td><?php echo $row['borrower_username']; ?></td>
<td><?php echo $row['borrower_role']; ?></td>
<td><?php echo $row['issued_by']; ?></td>
<td><?php echo $row['issue_date']; ?></td>
<td><?php echo $row['status']; ?></td>

</tr>

<?php } ?>

</table>

</body>
</html>