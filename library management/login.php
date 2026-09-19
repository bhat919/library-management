```php
<?php
session_start();
require_once "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if ($username === "" || $password === "") {
        $error = "Please enter username and password.";
    } else {

        $stmt = $conn->prepare(
            "SELECT user_id, name, username, email, password, role, student_id
             FROM users
             WHERE username = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["name"] = $user["name"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = $user["role"];
                $_SESSION["student_id"] = $user["student_id"];

                if ($user["role"] === "admin") {

                    header("Location: admin_dashboard.php");
                    exit();

                } elseif ($user["role"] === "student") {

                    header("Location: student_dashboard.php");
                    exit();

                } else {

                    $error = "Invalid account role.";

                }

            } else {

                $error = "Incorrect username or password.";

            }

        } else {

            $error = "Incorrect username or password.";

        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login - Library Management System</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;

            font-family: Arial, Helvetica, sans-serif;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 25px;

            background:
                linear-gradient(
                    135deg,
                    rgba(3, 18, 42, 0.95),
                    rgba(13, 110, 253, 0.88)
                ),
                url("https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=1800&q=85");

            background-size: cover;
            background-position: center;
        }

        .login-wrapper {
            width: 100%;
            max-width: 950px;

            display: grid;
            grid-template-columns: 1fr 1fr;

            background: rgba(255,255,255,0.96);

            border-radius: 28px;

            overflow: hidden;

            box-shadow:
                0 30px 80px rgba(0,0,0,0.3);

            animation: showPage 0.8s ease;
        }

        /* LEFT SIDE */

        .login-info {
            padding: 55px 45px;

            display: flex;
            flex-direction: column;
            justify-content: center;

            background:
                linear-gradient(
                    145deg,
                    #071b3a,
                    #0d47a1
                );

            color: white;

            position: relative;
            overflow: hidden;
        }

        .login-info::before {
            content: "";

            position: absolute;

            width: 250px;
            height: 250px;

            border: 40px solid rgba(255,255,255,0.05);

            border-radius: 50%;

            top: -120px;
            right: -100px;
        }

        .login-info::after {
            content: "";

            position: absolute;

            width: 180px;
            height: 180px;

            border-radius: 50%;

            background: rgba(77,163,255,0.12);

            bottom: -80px;
            left: -70px;
        }

        .library-icon {
            width: 75px;
            height: 75px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 20px;

            background: rgba(255,255,255,0.12);

            font-size: 32px;

            color: #66b3ff;

            margin-bottom: 25px;

            position: relative;
            z-index: 2;
        }

        .login-info h1 {
            font-size: 38px;

            line-height: 1.2;

            margin-bottom: 18px;

            position: relative;
            z-index: 2;
        }

        .login-info h1 span {
            color: #66b3ff;
        }

        .login-info p {
            color: #d4e5fa;

            line-height: 1.8;

            position: relative;
            z-index: 2;
        }

        .info-list {
            list-style: none;

            margin-top: 30px;

            position: relative;
            z-index: 2;
        }

        .info-list li {
            margin-bottom: 15px;

            color: #e7f0fc;
        }

        .info-list i {
            color: #4da3ff;

            margin-right: 10px;
        }

        /* RIGHT SIDE */

        .login-box {
            padding: 55px 45px;

            background: white;
        }

        .login-box h2 {
            color: #071b3a;

            font-size: 32px;

            margin-bottom: 8px;
        }

        .login-box .subtitle {
            color: #7b8797;

            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;

            color: #344054;

            font-weight: 600;

            margin-bottom: 8px;
        }

        .input-box {
            position: relative;
        }

        .input-box i {
            position: absolute;

            left: 15px;
            top: 50%;

            transform: translateY(-50%);

            color: #7b8797;
        }

        .input-box input {
            width: 100%;

            padding: 14px 45px;

            border: 1px solid #d8e0eb;

            border-radius: 12px;

            outline: none;

            font-size: 15px;

            transition: 0.3s;
        }

        .input-box input:focus {
            border-color: #0d6efd;

            box-shadow:
                0 0 0 4px rgba(13,110,253,0.1);
        }

        .password-toggle {
            position: absolute;

            right: 15px;
            top: 50%;

            transform: translateY(-50%);

            cursor: pointer;

            color: #7b8797;
        }

        .login-btn {
            width: 100%;

            border: none;

            padding: 15px;

            border-radius: 12px;

            background:
                linear-gradient(
                    135deg,
                    #0d6efd,
                    #1688ff
                );

            color: white;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;

            box-shadow:
                0 10px 25px rgba(13,110,253,0.3);

            transition: 0.3s;
        }

        .login-btn:hover {
            transform: translateY(-3px);

            box-shadow:
                0 15px 35px rgba(13,110,253,0.4);
        }

        .register-link {
            text-align: center;

            margin-top: 25px;

            color: #7b8797;
        }

        .register-link a {
            color: #0d6efd;

            text-decoration: none;

            font-weight: bold;
        }

        .home-link {
            display: block;

            text-align: center;

            margin-top: 15px;

            color: #667085;

            text-decoration: none;

            font-size: 14px;
        }

        .home-link:hover,
        .register-link a:hover {
            color: #084298;
        }

        .error {
            padding: 12px 15px;

            margin-bottom: 20px;

            border-radius: 10px;

            background: #fff0f0;

            color: #c62828;

            font-size: 14px;

            animation: shake 0.4s ease;
        }

        @keyframes showPage {

            from {
                opacity: 0;
                transform: translateY(30px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }

        }

        @keyframes shake {

            0%, 100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }

        }

        @media (max-width: 750px) {

            .login-wrapper {
                grid-template-columns: 1fr;
            }

            .login-info {
                padding: 40px 30px;
            }

            .login-info h1 {
                font-size: 30px;
            }

            .login-box {
                padding: 40px 30px;
            }

        }

        @media (max-width: 450px) {

            body {
                padding: 10px;
            }

            .login-info {
                padding: 30px 22px;
            }

            .login-box {
                padding: 30px 22px;
            }

        }

    </style>

</head>

<body>

<div class="login-wrapper">

    <!-- LEFT -->

    <div class="login-info">

        <div class="library-icon">

            <i class="fas fa-book-open"></i>

        </div>

        <h1>
            Welcome Back to
            <span>Library</span>
        </h1>

        <p>
            Login to access your Library Management
            System and manage your books easily.
        </p>

        <ul class="info-list">

            <li>
                <i class="fas fa-check-circle"></i>
                Easy Book Management
            </li>

            <li>
                <i class="fas fa-check-circle"></i>
                Student Management
            </li>

            <li>
                <i class="fas fa-check-circle"></i>
                Issue & Return Books
            </li>

            <li>
                <i class="fas fa-check-circle"></i>
                Secure Account
            </li>

        </ul>

    </div>


    <!-- RIGHT -->

    <div class="login-box">

        <h2>Login</h2>

        <p class="subtitle">
            Enter your account details to continue.
        </p>

        <?php if ($error !== ""): ?>

            <div class="error">

                <i class="fas fa-circle-exclamation"></i>

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <form method="POST"
              onsubmit="return validateLogin()">

            <div class="form-group">

                <label>
                    Username
                </label>

                <div class="input-box">

                    <i class="fas fa-user"></i>

                    <input
                        type="text"
                        name="username"
                        id="username"
                        placeholder="Enter username"
                        autocomplete="username"
                    >

                </div>

            </div>


            <div class="form-group">

                <label>
                    Password
                </label>

                <div class="input-box">

                    <i class="fas fa-lock"></i>

                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Enter password"
                        autocomplete="current-password"
                    >

                    <span class="password-toggle"
                          onclick="togglePassword()">

                        <i id="eye"
                           class="fas fa-eye"></i>

                    </span>

                </div>

            </div>


            <button type="submit"
                    class="login-btn">

                <i class="fas fa-right-to-bracket"></i>

                Login

            </button>

        </form>


        <div class="register-link">

            Admin account nahi hai?

            <a href="register.php">
                Register
            </a>

        </div>


        <a href="index.php"
           class="home-link">

            <i class="fas fa-arrow-left"></i>

            Back to Home

        </a>

    </div>

</div>


<script>

function togglePassword() {

    const password =
        document.getElementById("password");

    const eye =
        document.getElementById("eye");

    if (password.type === "password") {

        password.type = "text";

        eye.classList.remove("fa-eye");

        eye.classList.add("fa-eye-slash");

    } else {

        password.type = "password";

        eye.classList.remove("fa-eye-slash");

        eye.classList.add("fa-eye");

    }

}


function validateLogin() {

    const username =
        document.getElementById("username").value.trim();

    const password =
        document.getElementById("password").value;

    if (username === "") {

        alert("Please enter your username.");

        return false;

    }

    if (password === "") {

        alert("Please enter your password.");

        return false;

    }

    return true;

}

</script>

</body>
</html>
```
