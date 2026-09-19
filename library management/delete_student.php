<?php

session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "admin") {

    header("Location: login.php");
    exit();
}


if (!isset($_GET["id"])) {

    header("Location: students.php");
    exit();
}


$id = (int)$_GET["id"];


$stmt = $conn->prepare(
    "DELETE FROM users
     WHERE user_id = ?
     AND role = 'student'"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$stmt->close();


header("Location: students.php");
exit();

?>