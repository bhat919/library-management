<?php

session_start();
require_once "db.php";

/* =========================
   ADMIN SECURITY
========================= */

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: login.php");
    exit();
}


/* =========================
   ADMIN NAME
========================= */

$adminName = $_SESSION["name"] ?? "Admin";


/* =========================
   STATISTICS
========================= */

// Total Students
$studentResult = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'student'"
);

$totalStudents = 0;

if ($studentResult) {
    $totalStudents = $studentResult->fetch_assoc()["total"];
}


// Total Books
$bookResult = $conn->query(
    "SELECT COUNT(*) AS total
     FROM books"
);

$totalBooks = 0;

if ($bookResult) {
    $totalBooks = $bookResult->fetch_assoc()["total"];
}


// Currently Issued Books
$issuedResult = $conn->query(
    "SELECT COUNT(*) AS total
     FROM issued_books
     WHERE status = 'Issued'"
);

$totalIssued = 0;

if ($issuedResult) {
    $totalIssued = $issuedResult->fetch_assoc()["total"];
}


/* =========================
   RECENT BOOK ACTIVITY
========================= */

$recentActivity = $conn->query(
    "SELECT
        issued_books.issue_id,
        issued_books.issue_date,
        issued_books.return_date,
        issued_books.status,

        users.student_id,
        users.name AS student_name,

        books.book_no,
        books.book_name,
        books.author

     FROM issued_books

     INNER JOIN users
     ON issued_books.user_id = users.user_id

     INNER JOIN books
     ON issued_books.book_id = books.book_id

     ORDER BY issued_books.issue_id DESC

     LIMIT 8"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - Library Management System</title>

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;

            background:
                linear-gradient(
                    rgba(4, 20, 45, 0.90),
                    rgba(4, 20, 45, 0.95)
                ),
                url("https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=1800&q=85")
                center / cover fixed no-repeat;

            color: #ffffff;

            min-height: 100vh;
        }


        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {

            width: 260px;

            height: 100vh;

            position: fixed;

            left: 0;
            top: 0;

            background:
                linear-gradient(
                    180deg,
                    #061631,
                    #082653
                );

            border-right: 1px solid rgba(255,255,255,0.10);

            padding: 25px 18px;

            z-index: 1000;

            box-shadow: 5px 0 25px rgba(0,0,0,0.25);
        }


        .logo {

            display: flex;

            align-items: center;

            gap: 12px;

            margin-bottom: 35px;

            padding: 0 10px;
        }


        .logo-icon {

            width: 45px;
            height: 45px;

            border-radius: 12px;

            display: flex;

            align-items: center;
            justify-content: center;

            background: #0d6efd;

            font-size: 20px;

            box-shadow:
                0 8px 20px rgba(13,110,253,0.35);
        }


        .logo h2 {

            font-size: 18px;

            line-height: 1.2;
        }


        .logo span {

            color: #70b7ff;

            font-size: 12px;
        }


        .menu-title {

            font-size: 11px;

            color: #8da8ca;

            text-transform: uppercase;

            letter-spacing: 1.5px;

            padding: 0 12px;

            margin-bottom: 10px;
        }


        .sidebar a {

            display: flex;

            align-items: center;

            gap: 14px;

            text-decoration: none;

            color: #c7d7ed;

            padding: 13px 14px;

            margin-bottom: 6px;

            border-radius: 10px;

            transition: 0.3s;

            font-size: 14px;
        }


        .sidebar a i {

            width: 20px;

            text-align: center;
        }


        .sidebar a:hover,
        .sidebar a.active {

            background: #0d6efd;

            color: white;

            transform: translateX(4px);

            box-shadow:
                0 7px 20px rgba(13,110,253,0.25);
        }


        .sidebar .logout {

            position: absolute;

            bottom: 25px;

            left: 18px;

            right: 18px;

            color: #ffb8b8;
        }


        .sidebar .logout:hover {

            background: #dc3545;

            color: white;
        }


        /* =========================
           MAIN
        ========================= */

        .main {

            margin-left: 260px;

            min-height: 100vh;

            padding: 25px 35px 30px;
        }


        /* =========================
           TOPBAR
        ========================= */

        .topbar {

            display: flex;

            justify-content: space-between;

            align-items: center;

            background:
                rgba(8, 38, 83, 0.82);

            border: 1px solid rgba(255,255,255,0.10);

            padding: 14px 20px;

            border-radius: 15px;

            backdrop-filter: blur(10px);

            margin-bottom: 25px;
        }


        .topbar-left h3 {

            font-size: 20px;

            margin-bottom: 4px;
        }


        .topbar-left p {

            color: #9eb6d5;

            font-size: 12px;
        }


        .admin-profile {

            display: flex;

            align-items: center;

            gap: 10px;
        }


        .profile-icon {

            width: 40px;
            height: 40px;

            border-radius: 50%;

            background: #0d6efd;

            display: flex;

            align-items: center;
            justify-content: center;
        }


        .profile-text strong {

            display: block;

            font-size: 13px;
        }


        .profile-text span {

            color: #8fa9c9;

            font-size: 11px;
        }


        /* =========================
           WELCOME
        ========================= */

        .welcome {

            position: relative;

            overflow: hidden;

            min-height: 220px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 35px;

            border-radius: 22px;

            background:
                linear-gradient(
                    135deg,
                    rgba(13,71,161,0.94),
                    rgba(6,32,70,0.95)
                );

            border: 1px solid rgba(255,255,255,0.10);

            box-shadow:
                0 20px 50px rgba(0,0,0,0.25);

            margin-bottom: 28px;
        }


        .welcome-content {

            max-width: 700px;

            position: relative;

            z-index: 2;
        }


        .welcome-badge {

            display: inline-block;

            background: rgba(255,255,255,0.12);

            border: 1px solid rgba(255,255,255,0.15);

            padding: 7px 13px;

            border-radius: 30px;

            font-size: 11px;

            margin-bottom: 15px;
        }


        .welcome h1 {

            font-size: 34px;

            margin-bottom: 12px;
        }


        .welcome h1 span {

            color: #71c4ff;
        }


        .welcome p {

            color: #c9dcf3;

            line-height: 1.7;

            font-size: 14px;

            max-width: 650px;
        }


        .welcome-icon {

            width: 150px;
            height: 150px;

            border-radius: 50%;

            display: flex;

            align-items: center;
            justify-content: center;

            background: rgba(255,255,255,0.08);

            border: 1px solid rgba(255,255,255,0.10);

            font-size: 70px;

            color: #72c5ff;

            margin-right: 30px;

            animation: float 3s ease-in-out infinite;
        }


        @keyframes float {

            0%,100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-12px);
            }
        }


        /* =========================
           STAT CARDS
        ========================= */

        .stats {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;

            margin-bottom: 28px;
        }


        .stat-card {

            position: relative;

            overflow: hidden;

            background:
                rgba(8, 38, 83, 0.85);

            border: 1px solid rgba(255,255,255,0.09);

            border-radius: 18px;

            padding: 24px;

            transition: 0.3s;

            backdrop-filter: blur(10px);
        }


        .stat-card:hover {

            transform: translateY(-7px);

            border-color: rgba(77,163,255,0.5);

            box-shadow:
                0 15px 35px rgba(0,0,0,0.25);
        }


        .stat-icon {

            width: 50px;
            height: 50px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 13px;

            background: rgba(13,110,253,0.20);

            color: #63b4ff;

            font-size: 21px;

            margin-bottom: 18px;
        }


        .stat-card h2 {

            font-size: 30px;

            margin-bottom: 5px;
        }


        .stat-card p {

            color: #91a9c7;

            font-size: 13px;
        }


        /* =========================
           QUICK ACTIONS
        ========================= */

        .section-title {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 15px;
        }


        .section-title h2 {

            font-size: 20px;
        }


        .section-title p {

            color: #8ea8c8;

            font-size: 12px;
        }


        .quick-actions {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 15px;

            margin-bottom: 30px;
        }


        .action-card {

            text-decoration: none;

            color: white;

            background:
                rgba(8, 38, 83, 0.82);

            border:
                1px solid rgba(255,255,255,0.09);

            border-radius: 16px;

            padding: 20px;

            transition: 0.3s;
        }


        .action-card:hover {

            transform: translateY(-6px);

            background:
                rgba(13,71,161,0.80);

            box-shadow:
                0 12px 30px rgba(0,0,0,0.25);
        }


        .action-card i {

            font-size: 25px;

            color: #63b4ff;

            margin-bottom: 15px;
        }


        .action-card h3 {

            font-size: 14px;

            margin-bottom: 7px;
        }


        .action-card p {

            color: #8fa9c8;

            font-size: 11px;

            line-height: 1.5;
        }


        /* =========================
           ACTIVITY TABLE
        ========================= */

        .activity-box {

            background:
                rgba(8, 38, 83, 0.85);

            border: 1px solid rgba(255,255,255,0.09);

            border-radius: 18px;

            overflow: hidden;

            margin-bottom: 30px;

            backdrop-filter: blur(10px);
        }


        .activity-header {

            padding: 20px 22px;

            border-bottom:
                1px solid rgba(255,255,255,0.08);
        }


        .activity-header h2 {

            font-size: 18px;

            margin-bottom: 5px;
        }


        .activity-header p {

            color: #8fa9c8;

            font-size: 12px;
        }


        .table-wrapper {

            width: 100%;

            overflow-x: auto;
        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 750px;
        }


        th {

            text-align: left;

            color: #86a4c7;

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: 0.5px;

            padding: 15px 20px;

            background:
                rgba(0,0,0,0.12);
        }


        td {

            padding: 15px 20px;

            border-top:
                1px solid rgba(255,255,255,0.06);

            font-size: 13px;

            color: #d5e2f1;
        }


        tr {

            transition: 0.25s;
        }


        tbody tr:hover {

            background:
                rgba(13,110,253,0.08);
        }


        .book-number {

            display: inline-block;

            background:
                rgba(77,163,255,0.12);

            color: #69b8ff;

            padding: 5px 9px;

            border-radius: 7px;

            font-size: 11px;

            font-weight: bold;
        }


        .status {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 10px;

            font-weight: bold;
        }


        .status.issued {

            color: #ffd166;

            background:
                rgba(255,209,102,0.12);
        }


        .status.returned {

            color: #69e6a2;

            background:
                rgba(25,135,84,0.15);
        }


        .empty {

            text-align: center;

            padding: 45px 20px;

            color: #8fa9c8;
        }


        .empty i {

            font-size: 35px;

            margin-bottom: 12px;

            color: #4d8dca;
        }


        /* =========================
           FOOTER
        ========================= */

        footer {

            text-align: center;

            padding: 20px;

            color: #7893b4;

            font-size: 12px;

            border-top:
                1px solid rgba(255,255,255,0.08);

            margin-top: 15px;
        }


        footer span {

            color: #5eaeff;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1100px) {

            .quick-actions {

                grid-template-columns:
                    repeat(2, 1fr);
            }

            .stats {

                grid-template-columns:
                    repeat(3, 1fr);
            }

            .welcome-icon {

                margin-right: 0;
            }
        }


        @media (max-width: 850px) {

            .sidebar {

                width: 75px;

                padding: 20px 10px;
            }


            .logo {

                justify-content: center;

                padding: 0;
            }


            .logo h2,
            .logo span,
            .menu-title,
            .sidebar a span {

                display: none;
            }


            .logo-icon {

                width: 45px;
            }


            .sidebar a {

                justify-content: center;

                padding: 14px 5px;
            }


            .sidebar .logout {

                left: 10px;
                right: 10px;
            }


            .main {

                margin-left: 75px;

                padding: 20px;
            }


            .welcome {

                padding: 25px;
            }


            .welcome-icon {

                width: 110px;
                height: 110px;

                font-size: 50px;
            }
        }


        @media (max-width: 650px) {

            .main {

                padding: 12px;
            }


            .topbar {

                padding: 12px;
            }


            .topbar-left h3 {

                font-size: 16px;
            }


            .profile-text {

                display: none;
            }


            .welcome {

                display: block;

                text-align: center;

                padding: 25px 18px;
            }


            .welcome h1 {

                font-size: 26px;
            }


            .welcome-icon {

                margin: 25px auto 0;
            }


            .stats {

                grid-template-columns: 1fr;

            }


            .quick-actions {

                grid-template-columns: 1fr;
            }
        }

    </style>

</head>


<body>


<!-- =========================
     SIDEBAR
========================= -->

<aside class="sidebar">

    <div class="logo">

        <div class="logo-icon">
            <i class="fa-solid fa-book-open"></i>
        </div>

        <div>
            <h2>Library</h2>
            <span>Management System</span>
        </div>

    </div>


    <div class="menu-title">
        Main Menu
    </div>


    <a href="admin_dashboard.php" class="active">

        <i class="fa-solid fa-chart-line"></i>

        <span>Dashboard</span>

    </a>


    <a href="add_student.php">

        <i class="fa-solid fa-user-plus"></i>

        <span>Add Student</span>

    </a>


    <a href="students.php">

        <i class="fa-solid fa-users"></i>

        <span>All Students</span>

    </a>


    <a href="add_book.php">

        <i class="fa-solid fa-book-medical"></i>

        <span>Add Book</span>

    </a>


    <a href="books.php">

        <i class="fa-solid fa-book"></i>

        <span>All Books</span>

    </a>


    <a href="issue_book.php">

        <i class="fa-solid fa-arrow-right-from-bracket"></i>

        <span>Issue Book</span>

    </a>


    <a href="return_book.php">

        <i class="fa-solid fa-arrow-left"></i>

        <span>Return Book</span>

    </a>


    <a href="logout.php" class="logout">

        <i class="fa-solid fa-right-from-bracket"></i>

        <span>Logout</span>

    </a>

</aside>


<!-- =========================
     MAIN CONTENT
========================= -->

<main class="main">


    <!-- TOPBAR -->

    <div class="topbar">

        <div class="topbar-left">

            <h3>Admin Dashboard</h3>

            <p>
                Manage your library from one place
            </p>

        </div>


        <div class="admin-profile">

            <div class="profile-icon">

                <i class="fa-solid fa-user-shield"></i>

            </div>


            <div class="profile-text">

                <strong>
                    <?php echo htmlspecialchars($adminName); ?>
                </strong>

                <span>
                    Administrator
                </span>

            </div>

        </div>

    </div>



    <!-- WELCOME -->

    <section class="welcome">

        <div class="welcome-content">

            <div class="welcome-badge">

                <i class="fa-solid fa-building-columns"></i>

                Library Management System

            </div>


            <h1>

                Welcome,

                <span>Admin!</span>

            </h1>


            <p>

                Manage students, books, book issues and returns
                easily from your library administration dashboard.
                Keep your library organized and accessible.

            </p>

        </div>


        <div class="welcome-icon">

            <i class="fa-solid fa-book-open-reader"></i>

        </div>

    </section>



    <!-- STATISTICS -->

    <div class="stats">


        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-solid fa-users"></i>

            </div>


            <h2 class="counter"
                data-target="<?php echo $totalStudents; ?>">

                0

            </h2>


            <p>
                Total Students
            </p>

        </div>



        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-solid fa-book"></i>

            </div>


            <h2 class="counter"
                data-target="<?php echo $totalBooks; ?>">

                0

            </h2>


            <p>
                Total Books
            </p>

        </div>



        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-solid fa-book-open"></i>

            </div>


            <h2 class="counter"
                data-target="<?php echo $totalIssued; ?>">

                0

            </h2>


            <p>
                Currently Issued
            </p>

        </div>

    </div>



    <!-- QUICK ACTIONS -->

    <div class="section-title">

        <div>

            <h2>
                Quick Actions
            </h2>

            <p>
                Common library operations
            </p>

        </div>

    </div>


    <div class="quick-actions">


        <a href="add_student.php"
           class="action-card">

            <i class="fa-solid fa-user-plus"></i>

            <h3>
                Add Student
            </h3>

            <p>
                Create a new student account
                with automatic Student ID.
            </p>

        </a>



        <a href="add_book.php"
           class="action-card">

            <i class="fa-solid fa-book-medical"></i>

            <h3>
                Add Book
            </h3>

            <p>
                Add a new book using
                your custom Book No.
            </p>

        </a>



        <a href="issue_book.php"
           class="action-card">

            <i class="fa-solid fa-arrow-right-from-bracket"></i>

            <h3>
                Issue Book
            </h3>

            <p>
                Issue an available book
                to a student.
            </p>

        </a>



        <a href="return_book.php"
           class="action-card">

            <i class="fa-solid fa-arrow-left"></i>

            <h3>
                Return Book
            </h3>

            <p>
                Manage currently issued
                books and returns.
            </p>

        </a>

    </div>



    <!-- RECENT ACTIVITY -->

    <div class="activity-box">


        <div class="activity-header">

            <h2>
                Recent Book Activity
            </h2>

            <p>
                Latest issue and return records
            </p>

        </div>


        <div class="table-wrapper">


            <?php if ($recentActivity && $recentActivity->num_rows > 0): ?>


                <table>

                    <thead>

                        <tr>

                            <th>Student</th>

                            <th>Book No.</th>

                            <th>Book Name</th>

                            <th>Author</th>

                            <th>Issue Date</th>

                            <th>Status</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while ($row = $recentActivity->fetch_assoc()): ?>


                        <tr>


                            <td>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $row["student_id"]
                                    );
                                    ?>
                                </strong>

                                <br>

                                <small style="color:#7893b4;">

                                    <?php
                                    echo htmlspecialchars(
                                        $row["student_name"]
                                    );
                                    ?>

                                </small>

                            </td>


                            <td>

                                <span class="book-number">

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
                                echo htmlspecialchars(
                                    $row["issue_date"]
                                );
                                ?>

                            </td>


                            <td>


                                <?php if ($row["status"] === "Issued"): ?>

                                    <span class="status issued">

                                        <i class="fa-solid fa-clock"></i>

                                        Issued

                                    </span>

                                <?php else: ?>

                                    <span class="status returned">

                                        <i class="fa-solid fa-check"></i>

                                        Returned

                                    </span>

                                <?php endif; ?>


                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>

                </table>


            <?php else: ?>


                <div class="empty">

                    <i class="fa-solid fa-book-open"></i>

                    <p>
                        No book activity available yet.
                    </p>

                </div>


            <?php endif; ?>


        </div>

    </div>



    <!-- FOOTER -->

    <footer>

        © <?php echo date("Y"); ?>

        <span>Library Management System</span>

        | Admin Dashboard

    </footer>


</main>



<!-- =========================
     JAVASCRIPT
========================= -->

<script>

    /* =========================
       COUNTER ANIMATION
    ========================= */

    const counters =
        document.querySelectorAll(".counter");


    counters.forEach(counter => {

        const target =
            parseInt(counter.dataset.target) || 0;

        let current = 0;

        const speed = 30;


        function updateCounter() {

            const increment =
                Math.max(1, Math.ceil(target / 30));


            current += increment;


            if (current >= target) {

                counter.innerText = target;

                return;

            }


            counter.innerText = current;

            setTimeout(updateCounter, speed);

        }


        updateCounter();

    });


    /* =========================
       PAGE LOAD ANIMATION
    ========================= */

    document.addEventListener(
        "DOMContentLoaded",
        function() {

            const cards =
                document.querySelectorAll(
                    ".stat-card, .action-card, .activity-box"
                );


            cards.forEach(
                (card, index) => {

                    card.style.opacity = "0";

                    card.style.transform =
                        "translateY(20px)";


                    setTimeout(
                        () => {

                            card.style.transition =
                                "0.5s ease";

                            card.style.opacity = "1";

                            card.style.transform =
                                "translateY(0)";

                        },
                        index * 100
                    );

                }
            );

        }
    );

</script>


</body>

</html>