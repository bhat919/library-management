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
   SEARCH
========================= */

$search = trim($_GET["search"] ?? "");


if ($search !== "") {

    $stmt = $conn->prepare(
        "SELECT
            book_id,
            book_no,
            book_name,
            author,
            created_at
         FROM books
         WHERE book_no LIKE ?
            OR book_name LIKE ?
            OR author LIKE ?
         ORDER BY book_id DESC"
    );

    $like = "%" . $search . "%";

    $stmt->bind_param(
        "sss",
        $like,
        $like,
        $like
    );

    $stmt->execute();

    $books = $stmt->get_result();

} else {

    $books = $conn->query(
        "SELECT
            book_id,
            book_no,
            book_name,
            author,
            created_at
         FROM books
         ORDER BY book_id DESC"
    );
}


/* =========================
   TOTAL BOOKS
========================= */

$totalResult = $conn->query(
    "SELECT COUNT(*) AS total FROM books"
);

$totalBooks = 0;

if ($totalResult) {
    $totalBooks = $totalResult->fetch_assoc()["total"];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>All Books - Library Management System</title>

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

            color: #ffffff;

            min-height: 100vh;

            background:
                linear-gradient(
                    rgba(4, 20, 45, 0.90),
                    rgba(4, 20, 45, 0.96)
                ),
                url("https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=1800&q=85")
                center / cover fixed no-repeat;
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

            padding: 25px 18px;

            background:
                linear-gradient(
                    180deg,
                    #061631,
                    #082653
                );

            border-right:
                1px solid rgba(255,255,255,0.10);

            box-shadow:
                5px 0 25px rgba(0,0,0,0.25);

            z-index: 1000;
        }


        .logo {

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 0 10px;

            margin-bottom: 35px;
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
        }


        .logo span {

            color: #70b7ff;

            font-size: 12px;
        }


        .menu-title {

            color: #8da8ca;

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: 1.5px;

            padding: 0 12px;

            margin-bottom: 10px;
        }


        .sidebar a {

            display: flex;

            align-items: center;

            gap: 14px;

            color: #c7d7ed;

            text-decoration: none;

            padding: 13px 14px;

            margin-bottom: 6px;

            border-radius: 10px;

            font-size: 14px;

            transition: 0.3s;
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

            left: 18px;
            right: 18px;
            bottom: 25px;

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

            padding: 14px 20px;

            margin-bottom: 25px;

            border-radius: 15px;

            background:
                rgba(8,38,83,0.82);

            border:
                1px solid rgba(255,255,255,0.10);

            backdrop-filter: blur(10px);
        }


        .topbar h3 {

            font-size: 20px;

            margin-bottom: 4px;
        }


        .topbar p {

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

            display: flex;

            align-items: center;
            justify-content: center;

            background: #0d6efd;
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
           PAGE HEADER
        ========================= */

        .page-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

            padding: 25px;

            border-radius: 20px;

            margin-bottom: 20px;

            background:
                linear-gradient(
                    135deg,
                    rgba(13,71,161,0.90),
                    rgba(6,32,70,0.94)
                );

            border:
                1px solid rgba(255,255,255,0.10);

            box-shadow:
                0 15px 40px rgba(0,0,0,0.20);
        }


        .page-header h1 {

            font-size: 28px;

            margin-bottom: 8px;
        }


        .page-header p {

            color: #c1d4eb;

            font-size: 13px;
        }


        .add-btn {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            color: white;

            text-decoration: none;

            background: #0d6efd;

            padding: 12px 18px;

            border-radius: 10px;

            font-size: 13px;

            font-weight: bold;

            transition: 0.3s;
        }


        .add-btn:hover {

            background: #0b5ed7;

            transform: translateY(-3px);

            box-shadow:
                0 8px 20px rgba(13,110,253,0.30);
        }


        /* =========================
           BOOK COUNT
        ========================= */

        .count-card {

            display: flex;

            align-items: center;

            gap: 15px;

            padding: 18px 20px;

            margin-bottom: 20px;

            border-radius: 15px;

            background:
                rgba(8,38,83,0.85);

            border:
                1px solid rgba(255,255,255,0.09);
        }


        .count-icon {

            width: 48px;
            height: 48px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 12px;

            color: #64b5ff;

            background:
                rgba(13,110,253,0.18);

            font-size: 20px;
        }


        .count-card h2 {

            font-size: 23px;

            margin-bottom: 3px;
        }


        .count-card p {

            color: #91a9c7;

            font-size: 12px;
        }


        /* =========================
           SEARCH
        ========================= */

        .search-box {

            display: flex;

            gap: 10px;

            margin-bottom: 20px;
        }


        .search-form {

            flex: 1;

            display: flex;

            gap: 10px;
        }


        .search-input {

            flex: 1;

            padding: 13px 15px;

            border-radius: 10px;

            border:
                1px solid rgba(255,255,255,0.12);

            outline: none;

            color: white;

            background:
                rgba(8,38,83,0.85);

            font-size: 13px;
        }


        .search-input::placeholder {

            color: #7893b4;
        }


        .search-input:focus {

            border-color: #0d6efd;

            box-shadow:
                0 0 0 3px rgba(13,110,253,0.12);
        }


        .search-btn {

            border: none;

            padding: 0 20px;

            border-radius: 10px;

            color: white;

            background: #0d6efd;

            cursor: pointer;

            font-size: 13px;

            transition: 0.3s;
        }


        .search-btn:hover {

            background: #0b5ed7;
        }


        .clear-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 0 18px;

            border-radius: 10px;

            color: #c5d8ee;

            text-decoration: none;

            background:
                rgba(255,255,255,0.08);

            border:
                1px solid rgba(255,255,255,0.10);

            font-size: 13px;

            transition: 0.3s;
        }


        .clear-btn:hover {

            background:
                rgba(255,255,255,0.14);
        }


        /* =========================
           TABLE
        ========================= */

        .books-box {

            background:
                rgba(8,38,83,0.85);

            border:
                1px solid rgba(255,255,255,0.09);

            border-radius: 18px;

            overflow: hidden;

            backdrop-filter: blur(10px);

            margin-bottom: 30px;
        }


        .box-header {

            padding: 20px;

            border-bottom:
                1px solid rgba(255,255,255,0.08);
        }


        .box-header h2 {

            font-size: 18px;

            margin-bottom: 5px;
        }


        .box-header p {

            color: #8fa9c8;

            font-size: 12px;
        }


        .table-wrapper {

            overflow-x: auto;
        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 750px;
        }


        th {

            text-align: left;

            padding: 15px 20px;

            color: #86a4c7;

            background:
                rgba(0,0,0,0.13);

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: 0.5px;
        }


        td {

            padding: 16px 20px;

            border-top:
                1px solid rgba(255,255,255,0.06);

            color: #d7e3f1;

            font-size: 13px;
        }


        tbody tr {

            transition: 0.25s;
        }


        tbody tr:hover {

            background:
                rgba(13,110,253,0.08);

            transform: scale(1.002);
        }


        .book-no {

            display: inline-block;

            padding: 7px 10px;

            border-radius: 7px;

            color: #6dbaff;

            background:
                rgba(77,163,255,0.12);

            font-weight: bold;

            font-size: 12px;
        }


        .book-name {

            font-weight: bold;

            color: #ffffff;

            margin-bottom: 4px;
        }


        .author {

            color: #91aac7;

            font-size: 12px;
        }


        .delete-btn {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            text-decoration: none;

            color: #ffb0b0;

            background:
                rgba(220,53,69,0.12);

            border:
                1px solid rgba(220,53,69,0.20);

            padding: 7px 11px;

            border-radius: 7px;

            font-size: 11px;

            transition: 0.3s;
        }


        .delete-btn:hover {

            background: #dc3545;

            color: white;

            transform: translateY(-2px);
        }


        .empty {

            text-align: center;

            padding: 60px 20px;

            color: #8fa9c8;
        }


        .empty i {

            display: block;

            font-size: 45px;

            color: #4d8dca;

            margin-bottom: 15px;
        }


        .empty h3 {

            color: white;

            margin-bottom: 8px;
        }


        .empty p {

            font-size: 13px;

            margin-bottom: 20px;
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
        }


        footer span {

            color: #5eaeff;
        }


        /* =========================
           RESPONSIVE
        ========================= */

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


            .page-header {

                align-items: flex-start;

                flex-direction: column;
            }
        }


        @media (max-width: 600px) {

            .main {

                padding: 12px;
            }


            .topbar {

                padding: 12px;
            }


            .profile-text {

                display: none;
            }


            .page-header h1 {

                font-size: 23px;
            }


            .search-form {

                flex-direction: column;
            }


            .search-btn {

                padding: 12px;
            }


            .clear-btn {

                padding: 12px;
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


    <a href="admin_dashboard.php">

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


    <a href="books.php" class="active">

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
     MAIN
========================= -->

<main class="main">


    <!-- TOPBAR -->

    <div class="topbar">

        <div>

            <h3>
                All Books
            </h3>

            <p>
                Manage your library books
            </p>

        </div>


        <div class="admin-profile">

            <div class="profile-icon">

                <i class="fa-solid fa-user-shield"></i>

            </div>


            <div class="profile-text">

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $_SESSION["name"] ?? "Admin"
                    );
                    ?>

                </strong>

                <span>
                    Administrator
                </span>

            </div>

        </div>

    </div>



    <!-- PAGE HEADER -->

    <section class="page-header">

        <div>

            <h1>

                <i class="fa-solid fa-book"></i>

                All Books

            </h1>

            <p>

                View, search and manage all books
                in your library.

            </p>

        </div>


        <a href="add_book.php"
           class="add-btn">

            <i class="fa-solid fa-plus"></i>

            Add New Book

        </a>

    </section>



    <!-- COUNT -->

    <div class="count-card">

        <div class="count-icon">

            <i class="fa-solid fa-book-open"></i>

        </div>


        <div>

            <h2>

                <?php
                echo $totalBooks;
                ?>

            </h2>

            <p>
                Total Books in Library
            </p>

        </div>

    </div>



    <!-- SEARCH -->

    <div class="search-box">

        <form
            method="GET"
            class="search-form"
        >

            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="Search by Book No., Book Name or Author..."
                value="<?php echo htmlspecialchars($search); ?>"
            >


            <button
                type="submit"
                class="search-btn"
            >

                <i class="fa-solid fa-magnifying-glass"></i>

                Search

            </button>

        </form>


        <?php if ($search !== ""): ?>

            <a
                href="books.php"
                class="clear-btn"
            >

                <i class="fa-solid fa-xmark"></i>

                Clear

            </a>

        <?php endif; ?>

    </div>



    <!-- BOOK TABLE -->

    <section class="books-box">


        <div class="box-header">

            <h2>
                Book List
            </h2>

            <p>

                <?php

                if ($search !== "") {

                    echo "Search results for: ";

                    echo htmlspecialchars($search);

                } else {

                    echo "All books available in the library.";

                }

                ?>

            </p>

        </div>



        <div class="table-wrapper">


            <?php if ($books && $books->num_rows > 0): ?>


                <table>

                    <thead>

                        <tr>

                            <th>
                                Book No.
                            </th>

                            <th>
                                Book Name
                            </th>

                            <th>
                                Author
                            </th>

                            <th>
                                Added
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while ($book = $books->fetch_assoc()): ?>


                        <tr>


                            <!-- BOOK NO -->

                            <td>

                                <span class="book-no">

                                    <?php
                                    echo htmlspecialchars(
                                        $book["book_no"]
                                    );
                                    ?>

                                </span>

                            </td>



                            <!-- BOOK NAME -->

                            <td>

                                <div class="book-name">

                                    <?php
                                    echo htmlspecialchars(
                                        $book["book_name"]
                                    );
                                    ?>

                                </div>

                            </td>



                            <!-- AUTHOR -->

                            <td>

                                <span class="author">

                                    <i class="fa-solid fa-user-pen"></i>

                                    <?php
                                    echo htmlspecialchars(
                                        $book["author"]
                                    );
                                    ?>

                                </span>

                            </td>



                            <!-- DATE -->

                            <td>

                                <?php

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $book["created_at"]
                                    )
                                );

                                ?>

                            </td>



                            <!-- DELETE -->

                            <td>

                                <a
                                    href="delete_book.php?id=<?php echo $book["book_id"]; ?>"
                                    class="delete-btn"
                                    onclick="return confirmDelete('<?php echo htmlspecialchars($book["book_name"], ENT_QUOTES); ?>');"
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

                    <i class="fa-solid fa-book-open"></i>

                    <h3>
                        No Books Found
                    </h3>

                    <p>

                        <?php

                        if ($search !== "") {

                            echo "No book matches your search.";

                        } else {

                            echo "You have not added any books yet.";

                        }

                        ?>

                    </p>


                    <?php if ($search === ""): ?>

                        <a
                            href="add_book.php"
                            class="add-btn"
                        >

                            <i class="fa-solid fa-plus"></i>

                            Add First Book

                        </a>

                    <?php endif; ?>

                </div>


            <?php endif; ?>


        </div>

    </section>



    <!-- FOOTER -->

    <footer>

        © <?php echo date("Y"); ?>

        <span>Library Management System</span>

        | All Books

    </footer>


</main>



<!-- =========================
     JAVASCRIPT
========================= -->

<script>

    function confirmDelete(bookName) {

        return confirm(
            "Are you sure you want to delete \"" +
            bookName +
            "\"?"
        );

    }


    /* Page animation */

    document.addEventListener(
        "DOMContentLoaded",
        function() {

            const elements =
                document.querySelectorAll(
                    ".count-card, .books-box"
                );


            elements.forEach(
                (element, index) => {

                    element.style.opacity = "0";

                    element.style.transform =
                        "translateY(20px)";


                    setTimeout(
                        () => {

                            element.style.transition =
                                "0.5s ease";

                            element.style.opacity = "1";

                            element.style.transform =
                                "translateY(0)";

                        },
                        index * 150
                    );

                }
            );

        }
    );

</script>


</body>

</html>