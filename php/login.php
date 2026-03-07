<?php
session_start();
include('../db/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql = "SELECT * FROM users 
            WHERE username='$username' 
            AND password='$password'";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {

        $row = mysqli_fetch_assoc($result);
        $category = $row['category'];

        // Store session
        $_SESSION['username'] = $row['username'];
        $_SESSION['category'] = $category;

        // 🔔 Promotion message (ACCEPT / REJECT)
        $_SESSION['promotion_message'] = $row['promotion_message'];
        $_SESSION['promotion_status']  = $row['promotion_status'];

        // Redirect
        if ($category === "Teacher" || $category === "Teacher_Admin") {
            header("Location: tdashboard.php");
            exit();
        } elseif ($category === "Student") {
            header("Location: sdashboard.php");
            exit();
        } else {
            echo "Invalid User Category";
        }

    } else {
        echo "Invalid Login";
    }
}
?>