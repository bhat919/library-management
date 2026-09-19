<?php

session_start();
require_once "db.php";

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: login.php");
    exit();
}

$id = isset($_GET["id"])
    ? (int)$_GET["id"]
    : 0;

if ($id <= 0) {
    header("Location: books.php");
    exit();
}

/*
    Check whether this book is currently issued.
*/

$stmt = $conn->prepare(
    "SELECT issue_id
     FROM issued_books
     WHERE book_id = ?
     AND status = 'Issued'
     LIMIT 1"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

$isIssued = $result->num_rows > 0;

$stmt->close();

if (!$isIssued) {

    $stmt = $conn->prepare(
        "DELETE FROM books
         WHERE book_id = ?"
    );

    $stmt->bind_param("i", $id);

    $stmt->execute();

    $stmt->close();
}

header("Location: books.php");

exit();

?>