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

/* Get students */

$students = $conn->query(
    "SELECT
        user_id,
        student_id,
        name,
        username
     FROM users
     WHERE role = 'student'
     ORDER BY name ASC"
);

/* Get books */

$books = $conn->query(
    "SELECT
        book_id,
        book_no,
        book_name,
        author
     FROM books
     ORDER BY book_name ASC"
);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $user_id = (int)($_POST["user_id"] ?? 0);
    $book_id = (int)($_POST["book_id"] ?? 0);

    if ($user_id <= 0 || $book_id <= 0) {

        $error = "Please select a student and book.";

    } else {

        $stmt = $conn->prepare(
            "SELECT user_id
             FROM users
             WHERE user_id = ?
             AND role = 'student'
             LIMIT 1"
        );

        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $studentResult = $stmt->get_result();

        if ($studentResult->num_rows === 0) {

            $error = "Invalid student.";

            $stmt->close();

        } else {

            $stmt->close();

            /*
                Check book
            */

            $stmt = $conn->prepare(
                "SELECT book_id
                 FROM books
                 WHERE book_id = ?
                 LIMIT 1"
            );

            $stmt->bind_param("i", $book_id);
            $stmt->execute();

            $bookResult = $stmt->get_result();

            if ($bookResult->num_rows === 0) {

                $error = "Book not found.";

                $stmt->close();

            } else {

                $stmt->close();

                /*
                    Prevent same student from
                    having same book twice.
                */

                $stmt = $conn->prepare(
                    "SELECT issue_id
                     FROM issued_books
                     WHERE user_id = ?
                     AND book_id = ?
                     AND status = 'Issued'
                     LIMIT 1"
                );

                $stmt->bind_param(
                    "ii",
                    $user_id,
                    $book_id
                );

                $stmt->execute();

                $duplicate =
                    $stmt->get_result();

                if ($duplicate->num_rows > 0) {

                    $error =
                        "This student already has this book.";

                    $stmt->close();

                } else {

                    $stmt->close();

                    /*
                        Check if book is already issued.
                    */

                    $stmt = $conn->prepare(
                        "SELECT issue_id
                         FROM issued_books
                         WHERE book_id = ?
                         AND status = 'Issued'
                         LIMIT 1"
                    );

                    $stmt->bind_param(
                        "i",
                        $book_id
                    );

                    $stmt->execute();

                    $issuedResult =
                        $stmt->get_result();

                    if ($issuedResult->num_rows > 0) {

                        $error =
                            "This book is already issued.";

                        $stmt->close();

                    } else {

                        $stmt->close();

                        $conn->begin_transaction();

                        try {

                            $stmt = $conn->prepare(
                                "INSERT INTO issued_books
                                (book_id, user_id, issue_date, status)
                                VALUES (?, ?, CURDATE(), 'Issued')"
                            );

                            $stmt->bind_param(
                                "ii",
                                $book_id,
                                $user_id
                            );

                            if (!$stmt->execute()) {
                                throw new Exception(
                                    "Issue failed."
                                );
                            }

                            $stmt->close();

                            $conn->commit();

                            $success =
                                "Book issued successfully.";

                        } catch (Exception $e) {

                            $conn->rollback();

                            $error =
                                "Unable to issue book.";

                        }
                    }
                }
            }
        }
    }
}

/* Reload data after submission */

$students = $conn->query(
    "SELECT
        user_id,
        student_id,
        name,
        username
     FROM users
     WHERE role = 'student'
     ORDER BY name ASC"
);

$books = $conn->query(
    "SELECT
        book_id,
        book_no,
        book_name,
        author
     FROM books
     WHERE book_id NOT IN (
         SELECT book_id
         FROM issued_books
         WHERE status = 'Issued'
     )
     ORDER BY book_name ASC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Issue Book - Library Management</title>

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
            rgba(4,20,45,.91),
            rgba(4,20,45,.96)
        ),
        url("https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=1800&q=85")
        center/cover fixed no-repeat;

    color: white;
}

.sidebar {

    position: fixed;

    left: 0;
    top: 0;

    width: 250px;
    height: 100vh;

    background: rgba(3,18,42,.98);

    padding: 25px 15px;

    box-shadow: 5px 0 20px rgba(0,0,0,.25);
}

.logo {

    text-align: center;

    color: #4da3ff;

    font-size: 22px;

    font-weight: bold;

    margin-bottom: 35px;
}

.sidebar a {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 13px 15px;

    margin: 7px 0;

    color: #dbe7f6;

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

.logout {

    margin-top: 30px !important;
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

    background: rgba(5,25,55,.85);

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

    max-width: 700px;

    background: rgba(13,43,85,.92);

    padding: 35px;

    border-radius: 20px;

    box-shadow: 0 15px 40px rgba(0,0,0,.3);
}

.form-group {

    margin-bottom: 22px;
}

label {

    display: block;

    margin-bottom: 9px;

    font-weight: bold;
}

label i {

    color: #4da3ff;

    margin-right: 7px;
}

select {

    width: 100%;

    padding: 14px;

    border-radius: 10px;

    border: 1px solid #31577f;

    background: #071f40;

    color: white;

    outline: none;

    font-size: 15px;
}

select:focus {

    border-color: #4da3ff;
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

    transform: translateY(-2px);

    background: #0b5ed7;
}

.message {

    padding: 14px;

    border-radius: 10px;

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

.note {

    margin-top: 20px;

    padding: 15px;

    border-left: 4px solid #4da3ff;

    background: rgba(77,163,255,.08);

    color: #c8d8ed;

    line-height: 1.6;
}

footer {

    text-align: center;

    padding: 25px;

    color: #9fb1c8;
}

@media(max-width:800px) {

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

    <a href="add_book.php">

        <i class="fa-solid fa-book-medical"></i>
        Add Book

    </a>

    <a href="books.php">

        <i class="fa-solid fa-book"></i>
        All Books

    </a>

    <a href="issue_book.php" class="active">

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

        <h3>Issue Book</h3>

        <div class="admin">

            <div class="admin-icon">

                <i class="fa-solid fa-user-shield"></i>

            </div>

            <?php echo htmlspecialchars($_SESSION["name"]); ?>

        </div>

    </div>

    <div class="content">

        <div class="page-title">

            <h1>

                <i class="fa-solid fa-book-open"></i>

                Issue Book

            </h1>

            <p>
                Select a student and an available book.
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

            <form method="POST">

                <div class="form-group">

                    <label>

                        <i class="fa-solid fa-user"></i>

                        Select Student

                    </label>

                    <select name="user_id" required>

                        <option value="">
                            -- Select Student --
                        </option>

                        <?php while ($student = $students->fetch_assoc()): ?>

                            <option
                                value="<?php echo $student["user_id"]; ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $student["student_id"]
                                );
                                ?>

                                -

                                <?php
                                echo htmlspecialchars(
                                    $student["name"]
                                );
                                ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

                <div class="form-group">

                    <label>

                        <i class="fa-solid fa-book"></i>

                        Select Book

                    </label>

                    <select name="book_id" required>

                        <option value="">
                            -- Select Book --
                        </option>

                        <?php while ($book = $books->fetch_assoc()): ?>

                            <option
                                value="<?php echo $book["book_id"]; ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $book["book_no"]
                                );
                                ?>

                                -

                                <?php
                                echo htmlspecialchars(
                                    $book["book_name"]
                                );
                                ?>

                                |
                                <?php
                                echo htmlspecialchars(
                                    $book["author"]
                                );
                                ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

                <button type="submit">

                    <i class="fa-solid fa-book-open"></i>

                    Issue Book

                </button>

            </form>

            <div class="note">

                <strong>Note:</strong>

                Once a book is issued, it will no longer appear
                in the available-book list until it is returned.

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

</script>

</body>
</html>