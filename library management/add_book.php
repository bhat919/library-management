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

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $book_no = trim($_POST["book_no"] ?? "");
    $book_name = trim($_POST["book_name"] ?? "");
    $author = trim($_POST["author"] ?? "");

    if (
        $book_no === "" ||
        $book_name === "" ||
        $author === ""
    ) {

        $error = "Please fill all fields.";

    } else {

        // Check duplicate Book No.
        $stmt = $conn->prepare(
            "SELECT book_id
             FROM books
             WHERE book_no = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $book_no);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $error = "This Book No. already exists.";

            $stmt->close();

        } else {

            $stmt->close();

            $stmt = $conn->prepare(
                "INSERT INTO books
                (book_no, book_name, author)
                VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "sss",
                $book_no,
                $book_name,
                $author
            );

            if ($stmt->execute()) {

                $success = "Book added successfully.";

            } else {

                $error = "Unable to add book.";
            }

            $stmt->close();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Add Book - Library Management</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;

    min-height: 100vh;

    background:
        linear-gradient(
            rgba(4, 20, 45, .90),
            rgba(4, 20, 45, .95)
        ),
        url("https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=1800&q=85")
        center / cover fixed no-repeat;

    color: white;
}

.sidebar {
    position: fixed;
    left: 0;
    top: 0;

    width: 250px;
    height: 100vh;

    background: rgba(3, 18, 42, .97);

    padding: 25px 15px;

    box-shadow: 5px 0 20px rgba(0,0,0,.25);
}

.logo {
    text-align: center;
    font-size: 22px;
    font-weight: bold;

    color: #4da3ff;

    margin-bottom: 35px;
}

.logo i {
    margin-right: 7px;
}

.sidebar a {
    display: flex;
    align-items: center;

    gap: 12px;

    padding: 13px 15px;

    margin: 7px 0;

    color: #dce8f8;

    text-decoration: none;

    border-radius: 10px;

    transition: .3s;
}

.sidebar a:hover,
.sidebar a.active {
    background: #0d6efd;
    color: white;

    transform: translateX(5px);
}

.sidebar .logout {
    margin-top: 30px;
    background: rgba(220,53,69,.15);
    color: #ff9da7;
}

.main {
    margin-left: 250px;
    min-height: 100vh;
}

.topbar {
    height: 75px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 35px;

    background: rgba(5, 25, 55, .85);

    border-bottom: 1px solid rgba(255,255,255,.08);
}

.admin {
    display: flex;
    align-items: center;
    gap: 12px;
}

.admin-icon {
    width: 42px;
    height: 42px;

    border-radius: 50%;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #0d6efd;
}

.content {
    padding: 45px;
}

.page-title {
    margin-bottom: 30px;
}

.page-title h1 {
    font-size: 34px;
    margin-bottom: 8px;
}

.page-title p {
    color: #b9c9df;
}

.form-container {
    max-width: 650px;

    background: rgba(13,43,85,.90);

    padding: 35px;

    border-radius: 20px;

    box-shadow: 0 15px 40px rgba(0,0,0,.3);
}

.form-group {
    margin-bottom: 22px;
}

.form-group label {
    display: block;

    margin-bottom: 9px;

    font-weight: bold;
}

.form-group label i {
    color: #4da3ff;
    margin-right: 7px;
}

.form-group input {
    width: 100%;

    padding: 14px 16px;

    border: 1px solid #31577f;

    border-radius: 10px;

    background: #071f40;

    color: white;

    outline: none;

    font-size: 15px;
}

.form-group input:focus {
    border-color: #4da3ff;

    box-shadow:
        0 0 0 3px rgba(77,163,255,.12);
}

button {
    width: 100%;

    padding: 14px;

    border: none;

    border-radius: 10px;

    background: #0d6efd;

    color: white;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;

    transition: .3s;
}

button:hover {
    background: #0b5ed7;
    transform: translateY(-2px);
}

.message {
    padding: 13px 15px;

    border-radius: 9px;

    margin-bottom: 20px;
}

.error {
    background: rgba(220,53,69,.15);
    color: #ff9da7;
}

.success {
    background: rgba(25,135,84,.15);
    color: #7ee2ad;
}

.example {
    margin-top: 20px;

    padding: 15px;

    background: rgba(77,163,255,.08);

    border-left: 4px solid #4da3ff;

    border-radius: 8px;

    color: #c8d8ed;

    line-height: 1.6;
}

footer {
    text-align: center;

    padding: 25px;

    color: #9fb1c8;
}

@media(max-width: 800px) {

    .sidebar {
        position: relative;

        width: 100%;
        height: auto;
    }

    .main {
        margin-left: 0;
    }

    .content {
        padding: 25px;
    }

    .topbar {
        padding: 0 20px;
    }
}

</style>

</head>

<body>

<div class="sidebar">

    <div class="logo">
        <i class="fa-solid fa-book-open"></i>
        Library System
    </div>

    <a href="admin_dashboard.php">
        <i class="fa-solid fa-gauge"></i>
        Dashboard
    </a>

    <a href="add_student.php">
        <i class="fa-solid fa-user-plus"></i>
        Add Student
    </a>

    <a href="students.php">
        <i class="fa-solid fa-users"></i>
        All Students
    </a>

    <a href="add_book.php" class="active">
        <i class="fa-solid fa-book-medical"></i>
        Add Book
    </a>

    <a href="books.php">
        <i class="fa-solid fa-book"></i>
        All Books
    </a>

    <a href="issue_book.php">
        <i class="fa-solid fa-book-open"></i>
        Issue Book
    </a>

    <a href="return_book.php">
        <i class="fa-solid fa-rotate-left"></i>
        Return Book
    </a>

    <a href="logout.php" class="logout">
        <i class="fa-solid fa-right-from-bracket"></i>
        Logout
    </a>

</div>

<div class="main">

    <div class="topbar">

        <h3>Add New Book</h3>

        <div class="admin">

            <div class="admin-icon">
                <i class="fa-solid fa-user-shield"></i>
            </div>

            <span>
                <?php echo htmlspecialchars($_SESSION["name"]); ?>
            </span>

        </div>

    </div>

    <div class="content">

        <div class="page-title">

            <h1>
                <i class="fa-solid fa-book-medical"></i>
                Add Book
            </h1>

            <p>
                Add a new book using your custom Book No.
            </p>

        </div>

        <div class="form-container">

            <?php if ($error): ?>

                <div class="message error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>

            <?php if ($success): ?>

                <div class="message success">
                    <i class="fa-solid fa-circle-check"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>

            <?php endif; ?>

            <form method="POST" onsubmit="return validateBook()">

                <div class="form-group">

                    <label>
                        <i class="fa-solid fa-hashtag"></i>
                        Book No.
                    </label>

                    <input
                        type="text"
                        name="book_no"
                        id="book_no"
                        placeholder="Example: 00001"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        <i class="fa-solid fa-book"></i>
                        Book Name
                    </label>

                    <input
                        type="text"
                        name="book_name"
                        id="book_name"
                        placeholder="Enter book name"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        <i class="fa-solid fa-user-pen"></i>
                        Author
                    </label>

                    <input
                        type="text"
                        name="author"
                        id="author"
                        placeholder="Enter author name"
                        required
                    >

                </div>

                <button type="submit">

                    <i class="fa-solid fa-plus"></i>
                    Add Book

                </button>

            </form>

            <div class="example">

                <strong>Example:</strong><br>

                Book No.: <b>00001</b><br>
                Book Name: <b>Java Programming</b><br>
                Author: <b>James Gosling</b>

            </div>

        </div>

    </div>

    <footer>

        © <span id="year"></span>
        Library Management System

    </footer>

</div>

<script>

document.getElementById("year").textContent =
    new Date().getFullYear();

function validateBook() {

    const bookNo =
        document.getElementById("book_no").value.trim();

    const bookName =
        document.getElementById("book_name").value.trim();

    const author =
        document.getElementById("author").value.trim();

    if (!bookNo || !bookName || !author) {

        alert("Please fill all fields.");

        return false;
    }

    return true;
}

</script>

</body>
</html>