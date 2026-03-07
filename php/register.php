<?php
include('../db/config.php');

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = mysqli_real_escape_string($conn, $_POST['password']);
$category = mysqli_real_escape_string($conn, $_POST['category']);

/* Check if username already exists */
$checkUser = "SELECT * FROM users WHERE username='$username'";
$result = mysqli_query($conn, $checkUser);

if (mysqli_num_rows($result) > 0) {
    echo "<script>alert('Username already exists');</script>";
    exit;
}

/* If Teacher selected, verify from faculties table */
if ($category === "Teacher") {

    $stmt = $conn->prepare("SELECT id FROM faculties WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $facultyResult = $stmt->get_result();

    if ($facultyResult->num_rows === 0) {
        echo "<script>alert('You are not an authorized faculty');</script>";
        exit;
    }
}

/* Insert user */
$insert = "INSERT INTO users (username, password, category)
           VALUES ('$username', '$password', '$category')";

if (mysqli_query($conn, $insert)) {
    echo "<script>alert('Registration successful'); window.location='../login.html';</script>";
} else {
    echo "<script>alert('Registration failed');</script>";
}
?>