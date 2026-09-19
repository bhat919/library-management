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

$message = "";
$messageType = "";


/* =========================
   ADD STUDENT
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";


    /* Validation */

    if ($name === "" || $email === "" || $username === "" || $password === "") {

        $message = "Please fill all fields.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    } elseif (strlen($password) < 6) {

        $message = "Password must contain at least 6 characters.";
        $messageType = "error";

    } else {


        /* =========================
           CHECK DUPLICATE USERNAME
        ========================= */

        $stmt = $conn->prepare(
            "SELECT user_id
             FROM users
             WHERE username = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $username);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $message = "Username already exists.";
            $messageType = "error";

        } else {

            $stmt->close();


            /* =========================
               CHECK DUPLICATE EMAIL
            ========================= */

            $stmt = $conn->prepare(
                "SELECT user_id
                 FROM users
                 WHERE email = ?
                 LIMIT 1"
            );

            $stmt->bind_param("s", $email);

            $stmt->execute();

            $result = $stmt->get_result();


            if ($result->num_rows > 0) {

                $message = "Email already exists.";
                $messageType = "error";

            } else {

                $stmt->close();


                /* =========================
                   GENERATE STUDENT ID
                ========================= */

                $result = $conn->query(
                    "SELECT student_id
                     FROM users
                     WHERE student_id IS NOT NULL
                     AND student_id LIKE 'STU%'
                     ORDER BY user_id DESC
                     LIMIT 1"
                );


                if ($result && $result->num_rows > 0) {

                    $last = $result->fetch_assoc()["student_id"];

                    $number = (int) filter_var(
                        $last,
                        FILTER_SANITIZE_NUMBER_INT
                    );

                    $number++;

                } else {

                    $number = 1001;
                }


                $student_id = "STU" . $number;


                /* =========================
                   PASSWORD HASH
                ========================= */

                $hashedPassword =
                    password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );


                $role = "student";


                /* =========================
                   INSERT STUDENT
                ========================= */

                $stmt = $conn->prepare(
                    "INSERT INTO users
                    (
                        student_id,
                        name,
                        username,
                        email,
                        password,
                        role
                    )
                    VALUES (?, ?, ?, ?, ?, ?)"
                );


                $stmt->bind_param(
                    "ssssss",
                    $student_id,
                    $name,
                    $username,
                    $email,
                    $hashedPassword,
                    $role
                );


                if ($stmt->execute()) {

                    $message =
                        "Student added successfully! Student ID: "
                        . $student_id;

                    $messageType = "success";


                    /* Clear form values */

                    $name = "";
                    $email = "";
                    $username = "";

                } else {

                    $message =
                        "Something went wrong. Please try again.";

                    $messageType = "error";
                }

                $stmt->close();
            }
        }

        if (isset($stmt) && $stmt) {
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

    <title>Add Student | Library Management System</title>


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


        .page-intro .badge {

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
           FORM CONTAINER
        ========================= */

        .content-grid {

            display: grid;

            grid-template-columns:
                1fr 0.7fr;

            gap: 25px;

            align-items: start;
        }


        .form-card {

            background:
                rgba(255,255,255,0.97);

            border-radius: 22px;

            padding: 30px;

            box-shadow:
                0 15px 35px
                rgba(0,0,0,0.2);
        }


        .form-title {

            display: flex;

            align-items: center;

            gap: 12px;

            margin-bottom: 25px;
        }


        .form-title-icon {

            width: 48px;
            height: 48px;

            border-radius: 14px;

            display: flex;

            justify-content: center;
            align-items: center;

            color: #0d6efd;

            background: #eaf3ff;

            font-size: 20px;
        }


        .form-title h2 {

            color: #071b3a;

            font-size: 20px;
        }


        .form-title p {

            color: #718096;

            font-size: 12px;

            margin-top: 4px;
        }


        .form-group {

            margin-bottom: 19px;
        }


        .form-group label {

            display: block;

            color: #344054;

            font-size: 13px;

            font-weight: bold;

            margin-bottom: 8px;
        }


        .input-box {

            position: relative;
        }


        .input-box i {

            position: absolute;

            left: 15px;

            top: 50%;

            transform:
                translateY(-50%);

            color: #8293a8;

            font-size: 14px;
        }


        .input-box input {

            width: 100%;

            padding:
                13px 45px;

            border:
                1px solid #d8e0ea;

            border-radius: 12px;

            outline: none;

            font-size: 14px;

            color: #26364a;

            transition:
                0.3s;
        }


        .input-box input:focus {

            border-color: #0d6efd;

            box-shadow:
                0 0 0 4px
                rgba(13,110,253,0.09);
        }


        .password-toggle {

            position: absolute;

            right: 15px;

            top: 50%;

            transform:
                translateY(-50%);

            cursor: pointer;

            color: #8293a8;

            font-size: 14px;
        }


        .student-id-box {

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 14px 16px;

            border-radius: 12px;

            background:
                #eef6ff;

            border:
                1px solid #d5e8ff;

            margin-bottom: 20px;
        }


        .student-id-box i {

            color: #0d6efd;

            font-size: 20px;
        }


        .student-id-box strong {

            display: block;

            color: #071b3a;

            font-size: 13px;
        }


        .student-id-box span {

            color: #718096;

            font-size: 11px;
        }


        .submit-btn {

            width: 100%;

            border: none;

            padding: 14px;

            border-radius: 12px;

            cursor: pointer;

            color: white;

            font-size: 14px;

            font-weight: bold;

            background:
                linear-gradient(
                    135deg,
                    #0d6efd,
                    #146ee8
                );

            box-shadow:
                0 8px 20px
                rgba(13,110,253,0.25);

            transition:
                0.3s;
        }


        .submit-btn:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 12px 25px
                rgba(13,110,253,0.35);
        }


        /* =========================
           ALERT
        ========================= */

        .alert {

            padding:
                14px 17px;

            border-radius: 12px;

            margin-bottom: 20px;

            font-size: 13px;

            font-weight: bold;

            display: flex;

            align-items: center;

            gap: 10px;
        }


        .alert.success {

            color: #137333;

            background: #e4f7eb;

            border:
                1px solid #b9e7c8;
        }


        .alert.error {

            color: #b42318;

            background: #fff0ee;

            border:
                1px solid #ffd0ca;
        }


        /* =========================
           INFO CARD
        ========================= */

        .info-card {

            background:
                rgba(255,255,255,0.96);

            border-radius: 22px;

            padding: 30px;

            box-shadow:
                0 15px 35px
                rgba(0,0,0,0.2);
        }


        .info-card h2 {

            color: #071b3a;

            font-size: 20px;

            margin-bottom: 20px;
        }


        .info-item {

            display: flex;

            gap: 14px;

            padding: 15px 0;

            border-bottom:
                1px solid #edf1f5;
        }


        .info-item:last-child {

            border-bottom: none;
        }


        .info-icon {

            width: 42px;
            height: 42px;

            min-width: 42px;

            border-radius: 12px;

            display: flex;

            justify-content: center;
            align-items: center;

            background:
                #eaf3ff;

            color: #0d6efd;
        }


        .info-item h3 {

            color: #071b3a;

            font-size: 14px;

            margin-bottom: 5px;
        }


        .info-item p {

            color: #718096;

            font-size: 12px;

            line-height: 1.5;
        }


        .back-buttons {

            display: flex;

            gap: 10px;

            margin-top: 25px;
        }


        .back-btn {

            flex: 1;

            padding: 11px;

            border-radius: 10px;

            text-align: center;

            text-decoration: none;

            font-size: 12px;

            font-weight: bold;

            color: #0d6efd;

            background: #eaf3ff;

            transition: 0.3s;
        }


        .back-btn:hover {

            background: #dcecff;

            transform:
                translateY(-2px);
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

        @media (max-width: 1100px) {

            .content-grid {

                grid-template-columns: 1fr;
            }

            .stats {
                grid-template-columns:
                    repeat(2, 1fr);
            }
        }


        @media (max-width: 800px) {

            .sidebar {

                width: 220px;
            }

            .main {

                margin-left: 220px;

                padding: 20px;
            }

            .admin-footer {

                grid-template-columns: 1fr;
            }

            .copyright {

                text-align: left;
            }
        }


        @media (max-width: 600px) {

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

            .form-card,
            .info-card {

                padding: 22px;
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


    <a href="add_student.php"
       class="active">

        <i class="fa-solid fa-user-plus"></i>

        Add Student

    </a>


    <a href="students.php">

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


    <!-- TOP BAR -->

    <div class="topbar">


        <div>

            <h2>
                Add Student
            </h2>

            <p>
                Create a new student account
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

            <i class="fa-solid fa-user-plus"></i>

            Student Management

        </span>


        <h1>
            Add New Student
        </h1>


        <p>

            Create a student account for your library.
            A unique Student ID such as STU1001,
            STU1002 and STU1003 will be generated
            automatically.

        </p>


    </section>



    <!-- =========================
         CONTENT
    ========================= -->

    <div class="content-grid">


        <!-- FORM -->

        <div class="form-card">


            <div class="form-title">

                <div class="form-title-icon">

                    <i class="fa-solid fa-user-graduate"></i>

                </div>


                <div>

                    <h2>
                        Student Information
                    </h2>

                    <p>
                        Enter the student's details below
                    </p>

                </div>

            </div>



            <!-- MESSAGE -->

            <?php if ($message !== ""): ?>

                <div class="alert <?php echo $messageType; ?>">

                    <?php if ($messageType === "success"): ?>

                        <i class="fa-solid fa-circle-check"></i>

                    <?php else: ?>

                        <i class="fa-solid fa-circle-exclamation"></i>

                    <?php endif; ?>


                    <span>

                        <?php
                        echo htmlspecialchars($message);
                        ?>

                    </span>

                </div>

            <?php endif; ?>



            <!-- STUDENT ID -->

            <div class="student-id-box">

                <i class="fa-solid fa-id-card"></i>


                <div>

                    <strong>
                        Student ID
                    </strong>

                    <span>
                        Automatically generated after registration
                    </span>

                </div>

            </div>



            <!-- FORM -->

            <form method="POST"
                  id="studentForm"
                  onsubmit="return validateForm();">


                <!-- NAME -->

                <div class="form-group">

                    <label for="name">
                        Full Name
                    </label>


                    <div class="input-box">

                        <i class="fa-solid fa-user"></i>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Enter student's full name"
                            value="<?php
                            echo htmlspecialchars(
                                $name ?? ""
                            );
                            ?>"
                        >

                    </div>

                </div>



                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">
                        Email Address
                    </label>


                    <div class="input-box">

                        <i class="fa-solid fa-envelope"></i>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="student@example.com"
                            value="<?php
                            echo htmlspecialchars(
                                $email ?? ""
                            );
                            ?>"
                        >

                    </div>

                </div>



                <!-- USERNAME -->

                <div class="form-group">

                    <label for="username">
                        Username
                    </label>


                    <div class="input-box">

                        <i class="fa-solid fa-at"></i>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Create username"
                            value="<?php
                            echo htmlspecialchars(
                                $username ?? ""
                            );
                            ?>"
                        >

                    </div>

                </div>



                <!-- PASSWORD -->

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>


                    <div class="input-box">

                        <i class="fa-solid fa-lock"></i>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Minimum 6 characters"
                        >


                        <span
                            class="password-toggle"
                            onclick="togglePassword()"
                        >

                            <i
                                class="fa-solid fa-eye"
                                id="eyeIcon"
                            ></i>

                        </span>

                    </div>

                </div>



                <!-- SUBMIT -->

                <button
                    type="submit"
                    class="submit-btn"
                    id="submitBtn"
                >

                    <i class="fa-solid fa-user-plus"></i>

                    Create Student Account

                </button>


            </form>


        </div>



        <!-- INFORMATION CARD -->

        <div class="info-card">


            <h2>
                <i class="fa-solid fa-circle-info"></i>
                Student Account
            </h2>


            <div class="info-item">

                <div class="info-icon">

                    <i class="fa-solid fa-id-card"></i>

                </div>


                <div>

                    <h3>
                        Automatic Student ID
                    </h3>

                    <p>
                        The system automatically generates
                        a unique ID like STU1001.
                    </p>

                </div>

            </div>



            <div class="info-item">

                <div class="info-icon">

                    <i class="fa-solid fa-shield-halved"></i>

                </div>


                <div>

                    <h3>
                        Secure Password
                    </h3>

                    <p>
                        Student passwords are securely
                        hashed before being stored.
                    </p>

                </div>

            </div>



            <div class="info-item">

                <div class="info-icon">

                    <i class="fa-solid fa-right-to-bracket"></i>

                </div>


                <div>

                    <h3>
                        Student Login
                    </h3>

                    <p>
                        Students can use their username
                        and password to login.
                    </p>

                </div>

            </div>



            <div class="info-item">

                <div class="info-icon">

                    <i class="fa-solid fa-book"></i>

                </div>


                <div>

                    <h3>
                        Library Access
                    </h3>

                    <p>
                        Students can view their own
                        issued and returned book history.
                    </p>

                </div>

            </div>



            <div class="back-buttons">

                <a
                    href="admin_dashboard.php"
                    class="back-btn"
                >

                    <i class="fa-solid fa-arrow-left"></i>

                    Dashboard

                </a>


                <a
                    href="students.php"
                    class="back-btn"
                >

                    <i class="fa-solid fa-users"></i>

                    Students

                </a>

            </div>


        </div>


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
   PASSWORD SHOW / HIDE
========================= */

function togglePassword() {

    const password =
        document.getElementById("password");

    const icon =
        document.getElementById("eyeIcon");


    if (password.type === "password") {

        password.type = "text";

        icon.classList.remove("fa-eye");

        icon.classList.add("fa-eye-slash");

    } else {

        password.type = "password";

        icon.classList.remove("fa-eye-slash");

        icon.classList.add("fa-eye");
    }
}



/* =========================
   FORM VALIDATION
========================= */

function validateForm() {

    const name =
        document.getElementById("name").value.trim();

    const email =
        document.getElementById("email").value.trim();

    const username =
        document.getElementById("username").value.trim();

    const password =
        document.getElementById("password").value;


    if (name === "") {

        alert("Please enter student's name.");

        document.getElementById("name").focus();

        return false;
    }


    if (email === "") {

        alert("Please enter student's email.");

        document.getElementById("email").focus();

        return false;
    }


    const emailPattern =
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;


    if (!emailPattern.test(email)) {

        alert("Please enter a valid email address.");

        document.getElementById("email").focus();

        return false;
    }


    if (username === "") {

        alert("Please enter a username.");

        document.getElementById("username").focus();

        return false;
    }


    if (username.length < 3) {

        alert("Username must contain at least 3 characters.");

        document.getElementById("username").focus();

        return false;
    }


    if (password === "") {

        alert("Please enter a password.");

        document.getElementById("password").focus();

        return false;
    }


    if (password.length < 6) {

        alert("Password must contain at least 6 characters.");

        document.getElementById("password").focus();

        return false;
    }


    return true;
}



/* =========================
   AUTO HIDE SUCCESS MESSAGE
========================= */

setTimeout(function() {

    const alertBox =
        document.querySelector(".alert.success");

    if (alertBox) {

        alertBox.style.transition =
            "opacity 0.5s ease";

        alertBox.style.opacity = "0";

        setTimeout(function() {

            alertBox.style.display = "none";

        }, 500);

    }

}, 5000);



/* =========================
   BUTTON LOADING
========================= */

document
    .getElementById("studentForm")
    .addEventListener("submit", function(event) {

        if (!validateForm()) {

            event.preventDefault();

            return;
        }


        const button =
            document.getElementById("submitBtn");


        button.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin"></i> Creating Account...';


        button.disabled = true;

    });

</script>


</body>

</html>