<?php

session_start();
require_once "db.php";

/* Student Security */

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "student"
) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION["user_id"];


/* Student Information */

$stmt = $conn->prepare(
    "SELECT student_id, name, username, email
     FROM users
     WHERE user_id = ?
     AND role = 'student'
     LIMIT 1"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$userResult = $stmt->get_result();

$student = $userResult->fetch_assoc();

$stmt->close();


if (!$student) {

    session_destroy();

    header("Location: login.php");

    exit();
}


/* Currently Issued Count */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM issued_books
     WHERE user_id = ?
     AND status = 'Issued'"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$currentIssued =
    (int)$result->fetch_assoc()["total"];

$stmt->close();


/* Total History */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM issued_books
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$totalHistory =
    (int)$result->fetch_assoc()["total"];

$stmt->close();


/* Returned Count */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM issued_books
     WHERE user_id = ?
     AND status = 'Returned'"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$returnedBooks =
    (int)$result->fetch_assoc()["total"];

$stmt->close();


/* Student Book History */

$stmt = $conn->prepare(
    "SELECT
        issued_books.issue_id,
        issued_books.issue_date,
        issued_books.return_date,
        issued_books.status,

        books.book_name,
        books.author

     FROM issued_books

     INNER JOIN books
        ON issued_books.book_id = books.book_id

     WHERE issued_books.user_id = ?

     ORDER BY issued_books.issue_id DESC"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$history = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Student Dashboard | Library Management System
    </title>


    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }


        body {

            min-height: 100vh;

            background:
                linear-gradient(
                    rgba(4,20,45,0.88),
                    rgba(4,20,45,0.96)
                ),
                url("https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=1800&q=85")
                center / cover fixed no-repeat;

            color: white;
        }


        /* SIDEBAR */

        .sidebar {

            position: fixed;

            left: 0;
            top: 0;

            width: 250px;
            height: 100vh;

            background:
                rgba(3,16,36,0.98);

            padding: 25px 15px;

            border-right:
                1px solid rgba(255,255,255,0.08);

            z-index: 1000;
        }


        .logo {

            text-align: center;

            margin-bottom: 30px;
        }


        .logo i {

            font-size: 38px;

            color: #4da3ff;

            margin-bottom: 10px;
        }


        .logo h2 {

            font-size: 20px;
        }


        .logo span {

            color: #4da3ff;
        }


        .menu {

            list-style: none;
        }


        .menu li {

            margin: 8px 0;
        }


        .menu a {

            display: flex;

            align-items: center;

            gap: 13px;

            padding: 13px 15px;

            color: #d8e7ff;

            text-decoration: none;

            border-radius: 10px;

            transition: 0.3s;
        }


        .menu a:hover,
        .menu a.active {

            background: #0d6efd;

            color: white;

            transform: translateX(4px);
        }


        .menu i {

            width: 20px;
        }


        .logout {

            margin-top: 25px;

            border-top:
                1px solid rgba(255,255,255,0.1);

            padding-top: 15px;
        }


        .logout a {

            color: #ff8585;
        }


        /* MAIN */

        .main {

            margin-left: 250px;

            min-height: 100vh;
        }


        /* TOPBAR */

        .topbar {

            height: 75px;

            background:
                rgba(5,28,60,0.92);

            backdrop-filter: blur(10px);

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 0 35px;

            border-bottom:
                1px solid rgba(255,255,255,0.08);
        }


        .topbar h3 {

            font-size: 21px;
        }


        .student-profile {

            display: flex;

            align-items: center;

            gap: 12px;
        }


        .student-icon {

            width: 43px;
            height: 43px;

            border-radius: 50%;

            background: #0d6efd;

            display: flex;

            align-items: center;

            justify-content: center;
        }


        /* CONTENT */

        .content {

            padding: 40px;
        }


        /* WELCOME */

        .welcome {

            background:
                linear-gradient(
                    135deg,
                    rgba(13,110,253,0.25),
                    rgba(8,34,70,0.88)
                );

            border:
                1px solid rgba(77,163,255,0.20);

            border-radius: 20px;

            padding: 30px;

            margin-bottom: 28px;

            position: relative;

            overflow: hidden;

            animation: fadeUp 0.7s ease;
        }


        .welcome::after {

            content: "📚";

            position: absolute;

            right: 40px;

            top: 20px;

            font-size: 100px;

            opacity: 0.08;

            transform: rotate(-10deg);
        }


        .welcome-badge {

            display: inline-block;

            background:
                rgba(77,163,255,0.15);

            color: #69b0ff;

            padding: 7px 14px;

            border-radius: 20px;

            font-size: 13px;

            margin-bottom: 12px;
        }


        .welcome h1 {

            font-size: 32px;

            margin-bottom: 10px;
        }


        .welcome h1 span {

            color: #4da3ff;
        }


        .welcome p {

            color: #bfd0e6;

            max-width: 650px;

            line-height: 1.6;
        }


        /* STUDENT ID */

        .student-id {

            margin-top: 18px;

            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding: 9px 14px;

            background:
                rgba(0,0,0,0.18);

            border-radius: 9px;

            color: #dceaff;

            font-size: 14px;
        }


        .student-id i {

            color: #4da3ff;
        }


        /* STATS */

        .stats {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;

            margin-bottom: 28px;
        }


        .stat-card {

            background:
                rgba(8,34,70,0.90);

            border:
                1px solid rgba(255,255,255,0.08);

            border-radius: 17px;

            padding: 23px;

            display: flex;

            align-items: center;

            gap: 17px;

            transition: 0.3s;

            animation: fadeUp 0.8s ease;
        }


        .stat-card:hover {

            transform: translateY(-5px);

            box-shadow:
                0 15px 35px rgba(0,0,0,0.25);
        }


        .stat-icon {

            width: 55px;
            height: 55px;

            border-radius: 14px;

            background:
                rgba(13,110,253,0.18);

            color: #4da3ff;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 24px;
        }


        .stat-info span {

            display: block;

            color: #8ea4c0;

            font-size: 13px;

            margin-bottom: 5px;
        }


        .stat-info strong {

            font-size: 25px;
        }


        /* HISTORY CARD */

        .history-card {

            background:
                rgba(8,34,70,0.92);

            border:
                1px solid rgba(255,255,255,0.08);

            border-radius: 18px;

            overflow: hidden;

            box-shadow:
                0 15px 40px rgba(0,0,0,0.25);

            animation: fadeUp 0.9s ease;
        }


        .history-header {

            padding: 22px 25px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            border-bottom:
                1px solid rgba(255,255,255,0.08);
        }


        .history-title {

            display: flex;

            align-items: center;

            gap: 10px;
        }


        .history-title i {

            color: #4da3ff;
        }


        .history-count {

            color: #8ea4c0;

            font-size: 13px;
        }


        .table-wrapper {

            overflow-x: auto;
        }


        table {

            width: 100%;

            min-width: 800px;

            border-collapse: collapse;
        }


        th {

            text-align: left;

            padding: 16px 20px;

            color: #91b4df;

            font-size: 13px;

            text-transform: uppercase;

            letter-spacing: 0.5px;

            background:
                rgba(0,0,0,0.15);
        }


        td {

            padding: 17px 20px;

            border-top:
                1px solid rgba(255,255,255,0.06);

            color: #dceaff;

            font-size: 14px;
        }


        tbody tr {

            transition: 0.3s;
        }


        tbody tr:hover {

            background:
                rgba(13,110,253,0.08);
        }


        /* BOOK */

        .book {

            display: flex;

            align-items: center;

            gap: 11px;
        }


        .book-icon {

            width: 40px;
            height: 40px;

            border-radius: 9px;

            background:
                rgba(13,110,253,0.18);

            color: #4da3ff;

            display: flex;

            align-items: center;

            justify-content: center;
        }


        .book-name {

            font-weight: bold;

            color: white;
        }


        .author {

            color: #8ea4c0;

            font-size: 12px;

            margin-top: 3px;
        }


        /* STATUS */

        .status {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            padding: 7px 12px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;
        }


        .status.issued {

            background:
                rgba(255,193,7,0.13);

            color: #ffd35c;

            border:
                1px solid rgba(255,193,7,0.25);
        }


        .status.returned {

            background:
                rgba(25,135,84,0.14);

            color: #75e6a8;

            border:
                1px solid rgba(25,135,84,0.25);
        }


        /* EMPTY */

        .empty {

            text-align: center;

            padding: 65px 20px;

            color: #8ea4c0;
        }


        .empty i {

            font-size: 55px;

            color: #4da3ff;

            margin-bottom: 18px;
        }


        .empty h3 {

            color: white;

            margin-bottom: 8px;
        }


        /* FOOTER */

        footer {

            text-align: center;

            padding: 25px;

            color: #8ea4c0;

            font-size: 13px;
        }


        /* ANIMATION */

        @keyframes fadeUp {

            from {

                opacity: 0;

                transform: translateY(25px);
            }

            to {

                opacity: 1;

                transform: translateY(0);
            }
        }


        /* RESPONSIVE */

        @media (max-width: 900px) {

            .sidebar {

                width: 210px;
            }

            .main {

                margin-left: 210px;
            }

            .content {

                padding: 25px;
            }

            .stats {

                grid-template-columns: 1fr;
            }
        }


        @media (max-width: 650px) {

            .sidebar {

                position: relative;

                width: 100%;

                height: auto;
            }

            .main {

                margin-left: 0;
            }

            .topbar {

                padding: 0 18px;
            }

            .content {

                padding: 20px 15px;
            }

            .welcome h1 {

                font-size: 27px;
            }

            .welcome::after {

                right: 10px;

                font-size: 70px;
            }
        }

    </style>

</head>


<body>


<!-- SIDEBAR -->

<aside class="sidebar">

    <div class="logo">

        <i class="fa-solid fa-book-open"></i>

        <h2>
            Library <span>MS</span>
        </h2>

    </div>


    <ul class="menu">

        <li>

            <a href="student_dashboard.php"
               class="active">

                <i class="fa-solid fa-house"></i>

                Dashboard

            </a>

        </li>


        <li>

            <a href="#history">

                <i class="fa-solid fa-book-open-reader"></i>

                My Books

            </a>

        </li>


        <li class="logout">

            <a href="logout.php">

                <i class="fa-solid fa-right-from-bracket"></i>

                Logout

            </a>

        </li>

    </ul>

</aside>


<!-- MAIN -->

<main class="main">


    <!-- TOPBAR -->

    <div class="topbar">

        <h3>

            <i class="fa-solid fa-graduation-cap"></i>

            Student Dashboard

        </h3>


        <div class="student-profile">

            <div>

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $student["name"]
                    );
                    ?>

                </strong>

                <small style="display:block;color:#8ea4c0;">

                    Student

                </small>

            </div>


            <div class="student-icon">

                <i class="fa-solid fa-user-graduate"></i>

            </div>

        </div>

    </div>


    <!-- CONTENT -->

    <section class="content">


        <!-- WELCOME -->

        <div class="welcome">

            <span class="welcome-badge">

                <i class="fa-solid fa-book"></i>

                Library Management System

            </span>


            <h1>

                Welcome,

                <span>
                    <?php
                    echo htmlspecialchars(
                        $student["name"]
                    );
                    ?>
                </span>

                👋

            </h1>


            <p>

                Welcome to your library dashboard.
                Here you can view your issued books
                and complete book history.

            </p>


            <div class="student-id">

                <i class="fa-solid fa-id-card"></i>

                Student ID:

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $student["student_id"]
                    );
                    ?>

                </strong>

            </div>

        </div>


        <!-- STATS -->

        <div class="stats">


            <div class="stat-card">

                <div class="stat-icon">

                    <i class="fa-solid fa-book-open"></i>

                </div>


                <div class="stat-info">

                    <span>
                        Currently Issued
                    </span>

                    <strong class="counter"
                            data-value="<?php
                            echo $currentIssued;
                            ?>">

                        0

                    </strong>

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon">

                    <i class="fa-solid fa-clock-rotate-left"></i>

                </div>


                <div class="stat-info">

                    <span>
                        Total History
                    </span>

                    <strong class="counter"
                            data-value="<?php
                            echo $totalHistory;
                            ?>">

                        0

                    </strong>

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon">

                    <i class="fa-solid fa-circle-check"></i>

                </div>


                <div class="stat-info">

                    <span>
                        Returned Books
                    </span>

                    <strong class="counter"
                            data-value="<?php
                            echo $returnedBooks;
                            ?>">

                        0

                    </strong>

                </div>

            </div>


        </div>


        <!-- HISTORY -->

        <div class="history-card"
             id="history">


            <div class="history-header">

                <div class="history-title">

                    <i class="fa-solid fa-book-open-reader"></i>

                    <strong>
                        My Book History
                    </strong>

                </div>


                <span class="history-count">

                    <?php echo $totalHistory; ?>
                    Records

                </span>

            </div>


            <?php if (
                $history &&
                $history->num_rows > 0
            ) { ?>


                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    Book
                                </th>

                                <th>
                                    Issue Date
                                </th>

                                <th>
                                    Return Date
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php
                        while (
                            $row =
                            $history->fetch_assoc()
                        ) {
                        ?>


                            <tr>


                                <!-- BOOK -->

                                <td>

                                    <div class="book">

                                        <div class="book-icon">

                                            <i class="fa-solid fa-book"></i>

                                        </div>


                                        <div>

                                            <div class="book-name">

                                                <?php
                                                echo htmlspecialchars(
                                                    $row["book_name"]
                                                );
                                                ?>

                                            </div>


                                            <div class="author">

                                                <i class="fa-solid fa-pen"></i>

                                                <?php
                                                echo htmlspecialchars(
                                                    $row["author"]
                                                );
                                                ?>

                                            </div>

                                        </div>

                                    </div>

                                </td>


                                <!-- ISSUE DATE -->

                                <td>

                                    <?php
                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $row["issue_date"]
                                        )
                                    );
                                    ?>

                                </td>


                                <!-- RETURN DATE -->

                                <td>

                                    <?php

                                    if (
                                        $row["return_date"]
                                    ) {

                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $row["return_date"]
                                            )
                                        );

                                    } else {

                                        echo '<span style="color:#8ea4c0;">
                                                Not Returned
                                              </span>';
                                    }

                                    ?>

                                </td>


                                <!-- STATUS -->

                                <td>

                                    <?php
                                    if (
                                        $row["status"] === "Issued"
                                    ) {
                                    ?>

                                        <span class="status issued">

                                            <i class="fa-solid fa-clock"></i>

                                            Issued

                                        </span>

                                    <?php
                                    } else {
                                    ?>

                                        <span class="status returned">

                                            <i class="fa-solid fa-check"></i>

                                            Returned

                                        </span>

                                    <?php } ?>

                                </td>


                            </tr>


                        <?php } ?>


                        </tbody>

                    </table>

                </div>


            <?php } else { ?>


                <div class="empty">

                    <i class="fa-solid fa-book-open"></i>

                    <h3>
                        No Book History
                    </h3>

                    <p>
                        You have not been issued any books yet.
                    </p>

                </div>


            <?php } ?>


        </div>


    </section>


    <footer>

        © 2026 Library Management System | Student Panel

    </footer>


</main>


<script>

    /* Animated Counters */

    const counters =
        document.querySelectorAll(".counter");


    counters.forEach(function(counter) {

        const target =
            parseInt(
                counter.getAttribute("data-value")
            );


        let current = 0;


        if (target === 0) {

            counter.textContent = "0";

            return;
        }


        const increment =
            Math.max(
                1,
                Math.ceil(target / 20)
            );


        const timer =
            setInterval(function() {

                current += increment;


                if (current >= target) {

                    current = target;

                    clearInterval(timer);
                }


                counter.textContent =
                    current;

            }, 40);

    });


    /* Smooth Scroll */

    document
        .querySelectorAll('a[href^="#"]')
        .forEach(function(link) {

            link.addEventListener(
                "click",
                function(event) {

                    const target =
                        document.querySelector(
                            this.getAttribute("href")
                        );


                    if (target) {

                        event.preventDefault();

                        target.scrollIntoView({
                            behavior: "smooth"
                        });

                    }

                }
            );

        });

</script>


</body>

</html>