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

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $issue_id = (int)($_POST["issue_id"] ?? 0);

    if ($issue_id <= 0) {

        $message = "Invalid issue record.";
        $messageType = "error";

    } else {

        /*
            Get issue information
        */

        $stmt = $conn->prepare(
            "SELECT
                issue_id,
                book_id
             FROM issued_books
             WHERE issue_id = ?
             AND status = 'Issued'
             LIMIT 1"
        );

        $stmt->bind_param("i", $issue_id);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 0) {

            $message = "This book has already been returned.";
            $messageType = "error";

            $stmt->close();

        } else {

            $issue = $result->fetch_assoc();

            $book_id = (int)$issue["book_id"];

            $stmt->close();

            $conn->begin_transaction();

            try {

                /*
                    Mark book returned
                */

                $stmt = $conn->prepare(
                    "UPDATE issued_books
                     SET status = 'Returned',
                         return_date = CURDATE()
                     WHERE issue_id = ?
                     AND status = 'Issued'"
                );

                $stmt->bind_param(
                    "i",
                    $issue_id
                );

                if (!$stmt->execute()) {

                    throw new Exception(
                        "Return update failed."
                    );
                }

                $stmt->close();

                $conn->commit();

                $message =
                    "Book returned successfully.";

                $messageType = "success";

            } catch (Exception $e) {

                $conn->rollback();

                $message =
                    "Unable to return book.";

                $messageType = "error";
            }
        }
    }
}

/*
    Get currently issued books
*/

$stmt = $conn->prepare(
    "SELECT
        issued_books.issue_id,
        issued_books.issue_date,

        users.student_id,
        users.name,

        books.book_no,
        books.book_name,
        books.author

     FROM issued_books

     INNER JOIN users
        ON issued_books.user_id = users.user_id

     INNER JOIN books
        ON issued_books.book_id = books.book_id

     WHERE issued_books.status = 'Issued'

     ORDER BY issued_books.issue_id DESC"
);

$stmt->execute();

$issuedBooks = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Return Book - Library Management</title>

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

    padding: 25px 15px;

    background: rgba(3,18,42,.98);

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

    padding: 40px;
}

.heading {

    margin-bottom: 25px;
}

.heading h1 {

    font-size: 32px;

    margin-bottom: 8px;
}

.heading p {

    color: #b9c9df;
}

.message {

    padding: 14px 16px;

    border-radius: 10px;

    margin-bottom: 22px;
}

.success {

    background: rgba(25,135,84,.15);

    color: #7ee2ad;
}

.error {

    background: rgba(220,53,69,.15);

    color: #ff9da7;
}

.table-container {

    overflow-x: auto;

    background: rgba(13,43,85,.92);

    border-radius: 18px;

    box-shadow: 0 15px 35px rgba(0,0,0,.25);
}

table {

    width: 100%;

    border-collapse: collapse;

    min-width: 950px;
}

th {

    padding: 17px;

    text-align: left;

    background: #0b376b;

    color: #dceaff;
}

td {

    padding: 15px;

    border-bottom: 1px solid rgba(255,255,255,.08);

    color: #d0deef;
}

tr:hover td {

    background: rgba(77,163,255,.06);
}

.book-no {

    display: inline-block;

    padding: 6px 10px;

    border-radius: 7px;

    background: rgba(77,163,255,.12);

    color: #65b1ff;

    font-weight: bold;
}

.return-btn {

    border: none;

    padding: 9px 13px;

    border-radius: 8px;

    background: #198754;

    color: white;

    cursor: pointer;

    font-weight: bold;

    transition: .3s;
}

.return-btn:hover {

    background: #157347;

    transform: translateY(-2px);
}

.empty {

    text-align: center;

    padding: 60px;

    color: #abbcd1;
}

.empty i {

    font-size: 55px;

    color: #4da3ff;

    margin-bottom: 15px;
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

    <a href="issue_book.php">

        <i class="fa-solid fa-book-open"></i>
        Issue Book

    </a>

    <a href="return_book.php" class="active">

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

        <h3>Return Book</h3>

        <div class="admin">

            <div class="admin-icon">

                <i class="fa-solid fa-user-shield"></i>

            </div>

            <?php echo htmlspecialchars($_SESSION["name"]); ?>

        </div>

    </div>

    <div class="content">

        <div class="heading">

            <h1>

                <i class="fa-solid fa-rotate-left"></i>

                Return Book

            </h1>

            <p>
                Currently issued books are shown below.
            </p>

        </div>

        <?php if ($message): ?>

            <div class="message <?php echo $messageType; ?>">

                <?php if ($messageType === "success"): ?>

                    <i class="fa-solid fa-circle-check"></i>

                <?php else: ?>

                    <i class="fa-solid fa-circle-exclamation"></i>

                <?php endif; ?>

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>

        <div class="table-container">

            <?php if ($issuedBooks->num_rows > 0): ?>

            <table>

                <thead>

                    <tr>

                        <th>Student ID</th>
                        <th>Student</th>
                        <th>Book No.</th>
                        <th>Book Name</th>
                        <th>Author</th>
                        <th>Issue Date</th>
                        <th>Action</th>

                    </tr>

                </thead>

                <tbody>

                <?php while ($row = $issuedBooks->fetch_assoc()): ?>

                    <tr>

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $row["student_id"]
                            );
                            ?>

                        </td>

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $row["name"]
                            );
                            ?>

                        </td>

                        <td>

                            <span class="book-no">

                                <?php
                                echo htmlspecialchars(
                                    $row["book_no"]
                                );
                                ?>

                            </span>

                        </td>

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $row["book_name"]
                            );
                            ?>

                        </td>

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $row["author"]
                            );
                            ?>

                        </td>

                        <td>

                            <?php
                            echo date(
                                "d M Y",
                                strtotime($row["issue_date"])
                            );
                            ?>

                        </td>

                        <td>

                            <form
                                method="POST"
                                onsubmit="return confirmReturn('<?php echo htmlspecialchars($row["book_no"], ENT_QUOTES); ?>');"
                            >

                                <input
                                    type="hidden"
                                    name="issue_id"
                                    value="<?php echo $row["issue_id"]; ?>"
                                >

                                <button
                                    type="submit"
                                    class="return-btn"
                                >

                                    <i class="fa-solid fa-rotate-left"></i>

                                    Return

                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

            <?php else: ?>

                <div class="empty">

                    <i class="fa-solid fa-book-open"></i>

                    <h3>No Currently Issued Books</h3>

                    <p>
                        There are no books waiting to be returned.
                    </p>

                </div>

            <?php endif; ?>

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

function confirmReturn(bookNo) {

    return confirm(
        "Return Book No. " +
        bookNo +
        "?"
    );

}

</script>

</body>
</html>