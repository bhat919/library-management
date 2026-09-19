<?php

session_start();

if (isset($_SESSION["role"])) {

    // Admin
    if ($_SESSION["role"] === "admin") {
        header("Location: admin_dashboard.php");
        exit();
    }

    // Student
    if ($_SESSION["role"] === "student") {
        header("Location: student_dashboard.php");
        exit();
    }
}

// If not logged in
header("Location: login.php");
exit();

?>