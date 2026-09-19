```php
<?php
require_once "db.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if (
        $name === "" ||
        $email === "" ||
        $username === "" ||
        $password === "" ||
        $confirm_password === ""
    ) {

        $error = "Please fill all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (strlen($password) < 6) {

        $error = "Password must be at least 6 characters.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } else {

        $check = $conn->prepare(
            "SELECT user_id
             FROM users
             WHERE username = ? OR email = ?
             LIMIT 1"
        );

        $check->bind_param(
            "ss",
            $username,
            $email
        );

        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $error = "Username or email already exists.";

        } else {

            $hashedPassword =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

            $role = "admin";
            $student_id = NULL;

            $stmt = $conn->prepare(
                "INSERT INTO users
                (student_id, name, username, email, password, role)
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

                $success =
                    "Admin account created successfully!";

                $_POST = [];

            } else {

                $error =
                    "Registration failed. Please try again.";

            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Register - Library Management</title>

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
            justify-content: center;
            align-items: center;

            padding: 25px;

            background:
                linear-gradient(
                    135deg,
                    #061a38,
                    #0d6efd
                );
        }

        .register-wrapper {
            width: 100%;
            max-width: 1000px;

            display: grid;

            grid-template-columns: 0.9fr 1.1fr;

            background: white;

            border-radius: 28px;

            overflow: hidden;

            box-shadow:
                0 30px 80px rgba(0,0,0,0.3);

            animation: appear 0.8s ease;
        }

        /* LEFT */

        .register-info {
            padding: 50px 40px;

            color: white;

            background:
                linear-gradient(
                    145deg,
                    #071b3a,
                    #0d47a1
                );

            display: flex;

            flex-direction: column;

            justify-content: center;

            position: relative;

            overflow: hidden;
        }

        .register-info::before {
            content: "";

            position: absolute;

            width: 300px;
            height: 300px;

            border-radius: 50%;

            border: 45px solid rgba(255,255,255,0.05);

            top: -160px;
            right: -130px;
        }

        .register-icon {
            width: 75px;
            height: 75px;

            border-radius: 20px;

            background: rgba(255,255,255,0.12);

            display: flex;

            align-items: center;
            justify-content: center;

            color: #66b3ff;

            font-size: 32px;

            margin-bottom: 25px;
        }

        .register-info h1 {
            font-size: 37px;

            margin-bottom: 18px;

            line-height: 1.2;
        }

        .register-info h1 span {
            color: #66b3ff;
        }

        .register-info p {
            color: #d6e5f8;

            line-height: 1.8;

            margin-bottom: 25px;
        }

        .admin-badge {
            display: inline-flex;

            align-items: center;

            gap: 8px;

            width: fit-content;

            padding: 10px 15px;

            border-radius: 50px;

            background: rgba(255,255,255,0.12);

            color: #eaf4ff;

            font-size: 14px;
        }

        /* RIGHT */

        .register-box {
            padding: 45px;
        }

        .register-box h2 {
            color: #071b3a;

            font-size: 32px;

            margin-bottom: 7px;
        }

        .subtitle {
            color: #7b8797;

            margin-bottom: 28px;
        }

        .form-row {
            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 15px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;

            font-weight: 600;

            color: #344054;

            margin-bottom: 7px;
        }

        .input-box {
            position: relative;
        }

        .input-box > i {
            position: absolute;

            left: 14px;

            top: 50%;

            transform: translateY(-50%);

            color: #7b8797;
        }

        .input-box input {
            width: 100%;

            padding: 13px 42px;

            border: 1px solid #d8e0eb;

            border-radius: 11px;

            outline: none;

            font-size: 14px;

            transition: 0.3s;
        }

        .input-box input:focus {
            border-color: #0d6efd;

            box-shadow:
                0 0 0 4px rgba(13,110,253,0.1);
        }

        .password-eye {
            position: absolute;

            right: 14px;

            top: 50%;

            transform: translateY(-50%);

            cursor: pointer;

            color: #7b8797;
        }

        .register-btn {
            width: 100%;

            padding: 14px;

            border: none;

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

            transition: 0.3s;

            box-shadow:
                0 10px 25px rgba(13,110,253,0.3);
        }

        .register-btn:hover {
            transform: translateY(-3px);

            box-shadow:
                0 15px 35px rgba(13,110,253,0.4);
        }

        .message {
            padding: 12px 15px;

            border-radius: 10px;

            margin-bottom: 20px;

            font-size: 14px;
        }

        .error {
            background: #fff0f0;
            color: #c62828;
        }

        .success {
            background: #e9f9ef;
            color: #16834b;
        }

        .bottom-links {
            text-align: center;

            margin-top: 22px;

            color: #7b8797;
        }

        .bottom-links a {
            color: #0d6efd;

            text-decoration: none;

            font-weight: bold;
        }

        .home {
            display: block;

            text-align: center;

            margin-top: 13px;

            text-decoration: none;

            color: #667085;

            font-size: 14px;
        }

        @keyframes appear {

            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }

        }

        @media (max-width: 800px) {

            .register-wrapper {
                grid-template-columns: 1fr;
            }

            .register-info {
                padding: 35px 30px;
            }

            .register-box {
                padding: 35px 30px;
            }

        }

        @media (max-width: 500px) {

            body {
                padding: 10px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .register-box {
                padding: 28px 20px;
            }

        }

    </style>

</head>

<body>

<div class="register-wrapper">

    <!-- LEFT -->

    <div class="register-info">

        <div class="register-icon">

            <i class="fas fa-user-shield"></i>

        </div>

        <h1>

            Create Your
            <span>Admin Account</span>

        </h1>

        <p>

            Register as an administrator to manage
            students, books, issuing and returning
            in your library.

        </p>

        <div class="admin-badge">

            <i class="fas fa-shield-halved"></i>

            Administrator Registration

        </div>

    </div>


    <!-- RIGHT -->

    <div class="register-box">

        <h2>Admin Registration</h2>

        <p class="subtitle">
            Create your administrator account.
        </p>


        <?php if ($error !== ""): ?>

            <div class="message error">

                <i class="fas fa-circle-exclamation"></i>

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <?php if ($success !== ""): ?>

            <div class="message success">

                <i class="fas fa-circle-check"></i>

                <?= htmlspecialchars($success) ?>

            </div>

        <?php endif; ?>


        <form method="POST"
              onsubmit="return validateRegister()">

            <div class="form-row">

                <div class="form-group">

                    <label>
                        Full Name
                    </label>

                    <div class="input-box">

                        <i class="fas fa-user"></i>

                        <input
                            type="text"
                            name="name"
                            id="name"
                            placeholder="Enter full name"
                            value="<?= htmlspecialchars($_POST["name"] ?? "") ?>"
                        >

                    </div>

                </div>


                <div class="form-group">

                    <label>
                        Email
                    </label>

                    <div class="input-box">

                        <i class="fas fa-envelope"></i>

                        <input
                            type="email"
                            name="email"
                            id="email"
                            placeholder="Enter email"
                            value="<?= htmlspecialchars($_POST["email"] ?? "") ?>"
                        >

                    </div>

                </div>

            </div>


            <div class="form-group">

                <label>
                    Username
                </label>

                <div class="input-box">

                    <i class="fas fa-user-tag"></i>

                    <input
                        type="text"
                        name="username"
                        id="username"
                        placeholder="Create username"
                        value="<?= htmlspecialchars($_POST["username"] ?? "") ?>"
                    >

                </div>

            </div>


            <div class="form-row">

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
                            placeholder="Create password"
                        >

                        <span class="password-eye"
                              onclick="togglePassword('password','eye1')">

                            <i id="eye1"
                               class="fas fa-eye"></i>

                        </span>

                    </div>

                </div>


                <div class="form-group">

                    <label>
                        Confirm Password
                    </label>

                    <div class="input-box">

                        <i class="fas fa-lock"></i>

                        <input
                            type="password"
                            name="confirm_password"
                            id="confirm_password"
                            placeholder="Confirm password"
                        >

                        <span class="password-eye"
                              onclick="togglePassword('confirm_password','eye2')">

                            <i id="eye2"
                               class="fas fa-eye"></i>

                        </span>

                    </div>

                </div>

            </div>


            <button type="submit"
                    class="register-btn">

                <i class="fas fa-user-plus"></i>

                Create Admin Account

            </button>

        </form>


        <div class="bottom-links">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </div>


        <a href="index.php"
           class="home">

            <i class="fas fa-arrow-left"></i>

            Back to Home

        </a>

    </div>

</div>


<script>

function togglePassword(inputId, eyeId) {

    const input =
        document.getElementById(inputId);

    const eye =
        document.getElementById(eyeId);

    if (input.type === "password") {

        input.type = "text";

        eye.classList.remove("fa-eye");

        eye.classList.add("fa-eye-slash");

    } else {

        input.type = "password";

        eye.classList.remove("fa-eye-slash");

        eye.classList.add("fa-eye");

    }

}


function validateRegister() {

    const name =
        document.getElementById("name").value.trim();

    const email =
        document.getElementById("email").value.trim();

    const username =
        document.getElementById("username").value.trim();

    const password =
        document.getElementById("password").value;

    const confirm =
        document.getElementById("confirm_password").value;


    if (name === "") {

        alert("Please enter your full name.");

        return false;

    }


    if (email === "") {

        alert("Please enter your email.");

        return false;

    }


    if (username === "") {

        alert("Please create a username.");

        return false;

    }


    if (password.length < 6) {

        alert("Password must be at least 6 characters.");

        return false;

    }


    if (password !== confirm) {

        alert("Passwords do not match.");

        return false;

    }


    return true;

}

</script>

</body>
</html>
```
