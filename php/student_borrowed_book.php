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
<a href="../php/logout.php" class="nav-btn">Logout</a>
</div>

</header>


<div class="content">

<table>

<tr>
<th>Book ID</th>
<th>Title</th>
<th>Issued By</th>
<th>Issue Date</th>
<th>Due Date</th>
<th>Return Date</th>
<th>Status</th>
</tr>

<?php

$today = date("Y-m-d");

if($result->num_rows > 0){

    while($row = $result->fetch_assoc()){
        // Determine overdue status
        $is_overdue = ($row['status'] === 'borrowed' && $row['due_date'] < $today);
        $row_style = $is_overdue ? 'style="background:#ffebee; color:#c62828;"' : '';
        $status_display = $is_overdue ? 'Overdue' : htmlspecialchars($row['status']);

        echo "<tr $row_style>";
        echo "<td>".htmlspecialchars($row['book_id'])."</td>";
        echo "<td>".htmlspecialchars($row['book_title'])."</td>";
        echo "<td>".htmlspecialchars($row['issued_by'])."</td>";
        echo "<td>".htmlspecialchars($row['issue_date'])."</td>";
        echo "<td>".htmlspecialchars($row['due_date'] ?? 'N/A')."</td>";
        echo "<td>".htmlspecialchars($row['return_date'] ?? 'N/A')."</td>";
        echo "<td>".($is_overdue ? '<strong>'.$status_display.'</strong>' : $status_display)."</td>";
        echo "</tr>";
    }

}else{

    echo "<tr><td colspan='6'>No borrowed books yet</td></tr>";

}

?>

</table>

</div>

</body>
</html>