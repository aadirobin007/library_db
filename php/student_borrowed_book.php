<?php
session_start();
require '../db/config.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../login.html");
    exit();
}

$username = $_SESSION['username'];

$stmt = $conn->prepare("SELECT * FROM borrowed_books WHERE borrower_username=?");
$stmt->bind_param("s",$username);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>

<title>My Borrowed Books</title>

<link rel="stylesheet" href="../css/style.css">

</head>

<body>

<header class="top-header">

<h1>My Borrowed Books</h1>

<div>
<a href="sdashboard.php" class="nav-btn">Dashboard</a>
<a href="../logout.php" class="nav-btn">Logout</a>
</div>

</header>


<div class="content">

<table>

<tr>
<th>Book ID</th>
<th>Title</th>
<th>Issued By</th>
<th>Issue Date</th>
<th>Status</th>
</tr>

<?php

if($result->num_rows > 0){

while($row = $result->fetch_assoc()){

echo "<tr>";
echo "<td>".$row['book_id']."</td>";
echo "<td>".$row['book_title']."</td>";
echo "<td>".$row['issued_by']."</td>";
echo "<td>".$row['issue_date']."</td>";
echo "<td>".$row['status']."</td>";
echo "</tr>";

}

}else{

echo "<tr><td colspan='5'>No borrowed books yet</td></tr>";

}

?>

</table>

</div>

</body>
</html>