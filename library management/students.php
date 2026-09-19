```php
<?php

session_start();
require_once "db.php";

/* =========================
   ADMIN SECURITY
========================= */

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}


/* =========================
   SEARCH
========================= */

$search = trim($_GET["search"] ?? "");


if ($search !== "") {

    $stmt = $conn->prepare(
        "SELECT user_id, student_id, name, username, email, created_at
         FROM users
         WHERE role = 'student'
         AND (
             student_id LIKE ?
             OR name LIKE ?
             OR username LIKE ?
             OR email LIKE ?
         )
         ORDER BY user_id DESC"
    );

    $like = "%" . $search . "%";

    $stmt->bind_param(
        "ssss",
        $like,
        $like,
        $like,
        $like
    );

    $stmt->execute();

    $students = $stmt->get_result();

} else {

    $students = $conn->query(
        "SELECT user_id, student_id, name, username, email, created_at
         FROM users
         WHERE role = 'student'
         ORDER BY user_id DESC"
    );
}


/* =========================
   TOTAL STUDENTS
========================= */

$countResult = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'student'"
);

$totalStudents = $countResult->fetch_assoc()["total"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>All Students | Library Management System</title>


    <!-- Font Awesome -->

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


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

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            min-height: 100vh;

            color: #222;

            background:

                linear-gradient(
                    rgba(4, 20, 45, 0.88),
                    rgba(4, 20, 45, 0.94)
                ),

                url("https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=1800&q=85")

                center / cover fixed no-repeat;
        }


        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {

            position: fixed;

            left: 0;
            top: 0;

            width: 260px;

            height: 100vh;

            padding: 25px 18px;

            background:
                linear-gradient(
                    180deg,
                    #061a38,
                    #092957
                );

            color: white;

            box-shadow:
                5px 0 25px
                rgba(0,0,0,0.25);

            z-index: 1000;

            overflow-y: auto;
        }


        .logo {

            text-align: center;

            margin-bottom: 35px;

            padding-bottom: 25px;

            border-bottom:
                1px solid
                rgba(255,255,255,0.12);
        }


        .logo-icon {

            width: 65px;
            height: 65px;

            margin: auto;
            margin-bottom: 12px;

            border-radius: 18px;

            display: flex;

            justify-content: center;
            align-items: center;

            background:
                linear-gradient(
                    135deg,
                    #0d6efd,
                    #4da3ff
                );

            font-size: 30px;

            box-shadow:
                0 10px 25px
                rgba(13,110,253,0.35);
        }


        .logo h2 {

            font-size: 19px;
        }


        .logo p {

            color: #9fb6d2;

            font-size: 12px;

            margin-top: 6px;
        }


        .menu-title {

            color: #7893b2;

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: 1.5px;

            margin:
                20px 12px 10px;
        }


        .sidebar a {

            display: flex;

            align-items: center;

            gap: 13px;

            padding:
                13px 15px;

            margin: 5px 0;

            border-radius: 12px;

            color: #dbeafe;

            text-decoration: none;

            font-size: 14px;

            transition: 0.3s;
        }


        .sidebar a i {

            width: 20px;

            text-align: center;
        }


        .sidebar a:hover {

            background:
                rgba(13,110,253,0.22);

            color: white;

            transform:
                translateX(5px);
        }


        .sidebar a.active {

            background:
                linear-gradient(
                    135deg,
                    #0d6efd,
                    #146ee8
                );

            color: white;

            box-shadow:
                0 8px 20px
                rgba(13,110,253,0.25);
        }


        .logout {

            margin-top: 25px !important;

            color: #ffb4b4 !important;

            background:
                rgba(220,53,69,0.08);
        }


        .logout:hover {

            background:
                rgba(220,53,69,0.2) !important;
        }


        /* =========================
           MAIN
        ========================= */

        .main {

            margin-left: 260px;

            min-height: 100vh;

            padding: 30px 35px;
        }


        /* =========================
           TOPBAR
        ========================= */

        .topbar {

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            padding:
                15px 20px;

            margin-bottom: 25px;

            border-radius: 16px;

            background:
                rgba(255,255,255,0.96);

            box-shadow:
                0 10px 30px
                rgba(0,0,0,0.18);
        }


        .topbar h2 {

            color: #071b3a;

            font-size: 23px;
        }


        .topbar p {

            color: #718096;

            font-size: 13px;

            margin-top: 4px;
        }


        .admin-profile {

            display: flex;

            align-items: center;

            gap: 12px;
        }


        .admin-avatar {

            width: 45px;
            height: 45px;

            border-radius: 50%;

            display: flex;

            justify-content: center;
            align-items: center;

            color: white;

            background:
                linear-gradient(
                    135deg,
                    #0d6efd,
                    #4da3ff
                );
        }


        .admin-info strong {

            display: block;

            color: #071b3a;

            font-size: 14px;
        }


        .admin-info span {

            color: #718096;

            font-size: 12px;
        }


        /* =========================
           PAGE INTRO
        ========================= */

        .page-intro {

            padding: 35px;

            margin-bottom: 25px;

            border-radius: 23px;

            color: white;

            background:
                linear-gradient(
                    135deg,
                    rgba(13,71,161,0.96),
                    rgba(7,27,58,0.96)
                );

            box-shadow:
                0 15px 40px
                rgba(0,0,0,0.25);
        }


        .badge {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding:
                8px 14px;

            border-radius: 30px;

            background:
                rgba(255,255,255,0.12);

            font-size: 12px;

            margin-bottom: 13px;
        }


        .page-intro h1 {

            font-size: 34px;

            margin-bottom: 10px;
        }


        .page-intro p {

            color: #dbeafe;

            line-height: 1.7;

            max-width: 700px;

            font-size: 14px;
        }


        /* =========================
           TOOLBAR
        ========================= */

        .toolbar {

            background:
                rgba(255,255,255,0.97);

            padding: 20px;

            border-radius: 18px;

            box-shadow:
                0 10px 28px
                rgba(0,0,0,0.18);

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            gap: 15px;

            margin-bottom: 20px;
        }


        .search-form {

            display: flex;

            flex: 1;

            max-width: 550px;

            gap: 10px;
        }


        .search-box {

            position: relative;

            flex: 1;
        }


        .search-box i {

            position: absolute;

            left: 15px;

            top: 50%;

            transform:
                translateY(-50%);

            color: #8293a8;
        }


        .search-box input {

            width: 100%;

            padding:
                13px 15px 13px 43px;

            border:
                1px solid #d8e0ea;

            border-radius: 11px;

            outline: none;

            font-size: 13px;

            transition: 0.3s;
        }


        .search-box input:focus {

            border-color: #0d6efd;

            box-shadow:
                0 0 0 4px
                rgba(13,110,253,0.08);
        }


        .search-btn {

            padding:
                0 20px;

            border: none;

            border-radius: 11px;

            color: white;

            background:
                linear-gradient(
                    135deg,
                    #0d6efd,
                    #146ee8
                );

            cursor: pointer;

            font-weight: bold;

            transition: 0.3s;
        }


        .search-btn:hover {

            transform:
                translateY(-2px);
        }


        .add-btn {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding:
                13px 18px;

            border-radius: 11px;

            text-decoration: none;

            color: white;

            background:
                linear-gradient(
                    135deg,
                    #198754,
                    #157347
                );

            font-size: 13px;

            font-weight: bold;

            transition: 0.3s;
        }


        .add-btn:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 8px 18px
                rgba(25,135,84,0.25);
        }


        .total-box {

            color: #071b3a;

            font-size: 13px;

            font-weight: bold;

            white-space: nowrap;
        }


        .total-box span {

            color: #0d6efd;

            font-size: 20px;

            margin-left: 5px;
        }


        /* =========================
           TABLE CARD
        ========================= */

        .table-card {

            background:
                rgba(255,255,255,0.97);

            border-radius: 20px;

            padding: 25px;

            box-shadow:
                0 12px 30px
                rgba(0,0,0,0.18);

            overflow-x: auto;
        }


        table {

            width: 100%;

            min-width: 900px;

            border-collapse:
                collapse;
        }


        th {

            text-align: left;

            padding:
                15px 12px;

            background: #f5f8fc;

            color: #667085;

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: 0.6px;

            border-bottom:
                1px solid #e5eaf0;
        }


        th:first-child {

            border-radius:
                10px 0 0 10px;
        }


        th:last-child {

            border-radius:
                0 10px 10px 0;
        }


        td {

            padding:
                17px 12px;

            font-size: 13px;

            color: #344054;

            border-bottom:
                1px solid #edf1f5;
        }


        tbody tr {

            transition: 0.25s;
        }


        tbody tr:hover {

            background:
                #f8fbff;

            transform:
                scale(1.002);
        }


        .student-id {

            display: inline-block;

            padding:
                6px 10px;

            border-radius: 8px;

            color: #0d6efd;

            background: #eaf3ff;

            font-weight: bold;

            font-size: 11px;
        }


        .student-name {

            font-weight: bold;

            color: #071b3a;

            display: block;

            margin-bottom: 3px;
        }


        .username {

            color: #8a99aa;

            font-size: 11px;
        }


        .email {

            color: #475467;

            font-size: 12px;
        }


        .date {

            color: #667085;

            font-size: 12px;
        }


        /* =========================
           DELETE BUTTON
        ========================= */

        .delete-btn {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            padding:
                8px 12px;

            border-radius: 9px;

            text-decoration: none;

            color: #b42318;

            background: #fff0ee;

            border:
                1px solid #ffd5d0;

            font-size: 11px;

            font-weight: bold;

            transition: 0.3s;
        }


        .delete-btn:hover {

            background: #ffe2de;

            transform:
                translateY(-2px);
        }


        /* =========================
           EMPTY
        ========================= */

        .empty {

            text-align: center;

            padding: 60px 20px;

            color: #718096;
        }


        .empty i {

            font-size: 45px;

            color: #b8c7d9;

            margin-bottom: 15px;
        }


        .empty h3 {

            color: #344054;

            margin-bottom: 7px;
        }


        .empty p {

            font-size: 13px;

            margin-bottom: 18px;
        }


        /* =========================
           FOOTER
        ========================= */

        .admin-footer {

            margin-top: 45px;

            padding: 35px;

            border-radius:
                20px 20px 0 0;

            background:
                rgba(4,20,45,0.97);

            color: white;

            display: grid;

            grid-template-columns:
                1.5fr 1fr 1fr;

            gap: 30px;

            align-items: center;
        }


        .admin-footer h3 {

            font-size: 17px;

            margin-bottom: 10px;
        }


        .admin-footer h3 i {

            color: #4da3ff;

            margin-right: 6px;
        }


        .admin-footer p {

            color: #b8c7dd;

            font-size: 13px;

            line-height: 1.6;
        }


        .footer-links {

            display: flex;

            flex-direction: column;

            gap: 9px;
        }


        .footer-links a {

            color: #cfe3ff;

            text-decoration: none;

            font-size: 13px;

            transition: 0.3s;
        }


        .footer-links a:hover {

            color: #4da3ff;

            transform:
                translateX(5px);
        }


        .copyright {

            text-align: right;

            color: #8fa5c1;

            font-size: 13px;

            line-height: 1.7;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 950px) {

            .sidebar {

                width: 220px;
            }

            .main {

                margin-left: 220px;
            }

            .toolbar {

                flex-wrap: wrap;
            }

            .search-form {

                max-width: 100%;

                width: 100%;
            }

            .total-box {

                margin-left: auto;
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

                padding: 15px;
            }

            .topbar {

                flex-direction:
                    column;

                align-items:
                    flex-start;

                gap: 15px;
            }

            .page-intro {

                padding: 25px;
            }

            .page-intro h1 {

                font-size: 27px;
            }

            .toolbar {

                flex-direction:
                    column;

                align-items:
                    stretch;
            }

            .search-form {

                width: 100%;
            }

            .search-btn {

                padding:
                    0 15px;
            }

            .total-box {

                margin: 0;
            }

            .add-btn {

                justify-content: center;
            }

            .table-card {

                padding: 15px;
            }

            .admin-footer {

                grid-template-columns: 1fr;

                padding: 25px;
            }

            .copyright {

                text-align: left;
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

        <h2>
            Library System
        </h2>

        <p>
            Admin Panel
        </p>

    </div>


    <div class="menu-title">
        Main Menu
    </div>


    <a href="admin_dashboard.php">

        <i class="fa-solid fa-gauge-high"></i>

        Dashboard

    </a>


    <a href="add_student.php">

        <i class="fa-solid fa-user-plus"></i>

        Add Student

    </a>


    <a href="students.php"
       class="active">

        <i class="fa-solid fa-users"></i>

        All Students

    </a>


    <div class="menu-title">
        Library
    </div>


    <a href="add_book.php">

        <i class="fa-solid fa-book-medical"></i>

        Add Book

    </a>


    <a href="books.php">

        <i class="fa-solid fa-book"></i>

        All Books

    </a>


    <a href="issue_book.php">

        <i class="fa-solid fa-arrow-up-from-bracket"></i>

        Issue Book

    </a>


    <a href="return_book.php">

        <i class="fa-solid fa-arrow-down-to-bracket"></i>

        Return Book

    </a>


    <div class="menu-title">
        Account
    </div>


    <a href="logout.php"
       class="logout"
       onclick="return confirm('Are you sure you want to logout?');">

        <i class="fa-solid fa-right-from-bracket"></i>

        Logout

    </a>


</aside>



<!-- =========================
     MAIN
========================= -->

<main class="main">


    <!-- TOPBAR -->

    <div class="topbar">


        <div>

            <h2>
                All Students
            </h2>

            <p>
                View and manage registered students
            </p>

        </div>


        <div class="admin-profile">

            <div class="admin-avatar">

                <i class="fa-solid fa-user-shield"></i>

            </div>


            <div class="admin-info">

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $_SESSION["name"]
                    );
                    ?>

                </strong>

                <span>
                    Administrator
                </span>

            </div>

        </div>


    </div>



    <!-- =========================
         INTRO
    ========================= -->

    <section class="page-intro">


        <span class="badge">

            <i class="fa-solid fa-users"></i>

            Student Management

        </span>


        <h1>
            All Students
        </h1>


        <p>

            View all registered students in your
            library management system. You can search
            students by Student ID, name, username
            or email address.

        </p>


    </section>



    <!-- =========================
         TOOLBAR
    ========================= -->

    <div class="toolbar">


        <form
            method="GET"
            class="search-form"
        >


            <div class="search-box">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    name="search"
                    placeholder="Search student..."
                    value="<?php
                    echo htmlspecialchars($search);
                    ?>"
                >

            </div>


            <button
                type="submit"
                class="search-btn"
            >

                <i class="fa-solid fa-search"></i>

                Search

            </button>


        </form>


        <div class="total-box">

            Total Students:

            <span>
                <?php
                echo $totalStudents;
                ?>
            </span>

        </div>


        <a
            href="add_student.php"
            class="add-btn"
        >

            <i class="fa-solid fa-user-plus"></i>

            Add Student

        </a>


    </div>



    <!-- =========================
         STUDENT TABLE
    ========================= -->

    <div class="table-card">


        <?php if ($students && $students->num_rows > 0): ?>


            <table>


                <thead>

                    <tr>

                        <th>
                            Student ID
                        </th>

                        <th>
                            Student
                        </th>

                        <th>
                            Email
                        </th>

                        <th>
                            Created
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php while ($student = $students->fetch_assoc()): ?>


                    <tr>


                        <td>

                            <span class="student-id">

                                <?php
                                echo htmlspecialchars(
                                    $student["student_id"]
                                );
                                ?>

                            </span>

                        </td>


                        <td>

                            <span class="student-name">

                                <?php
                                echo htmlspecialchars(
                                    $student["name"]
                                );
                                ?>

                            </span>


                            <span class="username">

                                @<?php
                                echo htmlspecialchars(
                                    $student["username"]
                                );
                                ?>

                            </span>

                        </td>


                        <td>

                            <span class="email">

                                <i class="fa-solid fa-envelope"></i>

                                <?php
                                echo htmlspecialchars(
                                    $student["email"]
                                );
                                ?>

                            </span>

                        </td>


                        <td>

                            <span class="date">

                                <i class="fa-regular fa-calendar"></i>

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $student["created_at"]
                                    )
                                );
                                ?>

                            </span>

                        </td>


                        <td>

                            <a
                                href="delete_student.php?id=<?php echo $student["user_id"]; ?>"
                                class="delete-btn"
                                onclick="return confirmDelete('<?php echo htmlspecialchars($student["name"], ENT_QUOTES); ?>');"
                            >

                                <i class="fa-solid fa-trash"></i>

                                Delete

                            </a>

                        </td>


                    </tr>


                <?php endwhile; ?>


                </tbody>


            </table>


        <?php else: ?>


            <div class="empty">


                <i class="fa-solid fa-user-slash"></i>


                <h3>
                    No Students Found
                </h3>


                <p>

                    <?php if ($search !== ""): ?>

                        No student matched
                        "<?php
                        echo htmlspecialchars($search);
                        ?>".

                    <?php else: ?>

                        No students have been added yet.

                    <?php endif; ?>

                </p>


                <a
                    href="add_student.php"
                    class="add-btn"
                >

                    <i class="fa-solid fa-user-plus"></i>

                    Add First Student

                </a>


            </div>


        <?php endif; ?>


    </div>



    <!-- =========================
         FOOTER
    ========================= -->

    <footer class="admin-footer">


        <div>

            <h3>

                <i class="fa-solid fa-book-open"></i>

                Library Management System

            </h3>


            <p>

                Smart and simple library management
                for students and administrators.

            </p>

        </div>



        <div class="footer-links">

            <a href="admin_dashboard.php">
                Dashboard
            </a>

            <a href="add_student.php">
                Add Student
            </a>

            <a href="books.php">
                Books
            </a>

            <a href="logout.php">
                Logout
            </a>

        </div>



        <div class="copyright">

            © <?php echo date("Y"); ?>

            Library Management System

            <br>

            All Rights Reserved.

        </div>


    </footer>


</main>



<!-- =========================
     JAVASCRIPT
========================= -->

<script>


/* =========================
   DELETE CONFIRMATION
========================= */

function confirmDelete(name) {

    return confirm(
        "Are you sure you want to delete " +
        name +
        "?"
    );

}


/* =========================
   TABLE ANIMATION
========================= */

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const rows =
            document.querySelectorAll(
                "tbody tr"
            );


        rows.forEach(
            function(row, index) {

                row.style.opacity = "0";

                row.style.transform =
                    "translateY(15px)";


                setTimeout(
                    function() {

                        row.style.transition =
                            "all 0.45s ease";

                        row.style.opacity = "1";

                        row.style.transform =
                            "translateY(0)";

                    },
                    80 + (index * 70)
                );

            }
        );

    }
);

</script>


</body>

</html>
