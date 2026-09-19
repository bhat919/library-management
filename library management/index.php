
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Library Management System</title>

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>

        /* =========================
           BASIC
        ========================= */

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
            background: #f4f7fb;
            color: #172033;
        }

        /* =========================
           NAVBAR
        ========================= */

        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;

            min-height: 75px;
            padding: 15px 7%;

            display: flex;
            align-items: center;
            justify-content: space-between;

            background: rgba(5, 22, 48, 0.97);
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }

        .logo {
            color: white;
            font-size: 22px;
            font-weight: bold;

            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo i {
            color: #4da3ff;
            font-size: 28px;
        }

        .navbar nav {
            display: flex;
            gap: 8px;
        }

        .navbar nav a {
            color: #eaf3ff;
            text-decoration: none;

            padding: 10px 16px;
            border-radius: 10px;

            transition: 0.3s;
        }

        .navbar nav a:hover {
            background: #0d6efd;
            color: white;
            transform: translateY(-2px);
        }

        /* =========================
           HERO
        ========================= */

        .hero {
            min-height: 680px;

            display: flex;
            justify-content: center;
            align-items: center;

            text-align: center;

            padding: 80px 20px;

            position: relative;
            overflow: hidden;

            background:
                linear-gradient(
                    rgba(2, 16, 38, 0.78),
                    rgba(5, 45, 95, 0.85)
                ),
                url("https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=1800&q=85");

            background-size: cover;
            background-position: center;
        }

        .hero::before {
            content: "";

            position: absolute;

            width: 350px;
            height: 350px;

            border-radius: 50%;

            background: rgba(77,163,255,0.15);

            top: -150px;
            right: -100px;

            animation: floating 5s infinite ease-in-out;
        }

        .hero::after {
            content: "";

            position: absolute;

            width: 250px;
            height: 250px;

            border-radius: 50%;

            background: rgba(13,110,253,0.18);

            bottom: -120px;
            left: -80px;

            animation: floating 6s infinite ease-in-out reverse;
        }

        .hero-content {
            max-width: 850px;

            position: relative;
            z-index: 2;

            animation: fadeUp 1s ease;
        }

        .badge {
            display: inline-block;

            padding: 10px 20px;
            margin-bottom: 22px;

            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 50px;

            background: rgba(255,255,255,0.12);

            color: white;

            backdrop-filter: blur(10px);
        }

        .badge i {
            color: #66b3ff;
            margin-right: 7px;
        }

        .hero h1 {
            color: white;

            font-size: clamp(45px, 7vw, 75px);

            line-height: 1.05;

            margin-bottom: 25px;
        }

        .hero h1 span {
            display: block;
            color: #4da3ff;

            text-shadow: 0 0 30px rgba(77,163,255,0.5);
        }

        .hero p {
            max-width: 680px;
            margin: auto;

            color: #dceaff;

            font-size: 19px;

            margin-bottom: 35px;
        }

        /* =========================
           BUTTON
        ========================= */

        .hero-buttons {
            display: flex;

            justify-content: center;

            gap: 15px;

            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 9px;

            padding: 14px 25px;

            border-radius: 12px;

            background: linear-gradient(
                135deg,
                #0d6efd,
                #1688ff
            );

            color: white;

            text-decoration: none;

            font-weight: bold;

            box-shadow:
                0 10px 30px rgba(13,110,253,0.3);

            transition: 0.3s;
        }

        .btn:hover {
            transform: translateY(-5px);

            box-shadow:
                0 18px 40px rgba(13,110,253,0.4);
        }

        .btn.secondary {
            background: rgba(255,255,255,0.12);

            border: 1px solid rgba(255,255,255,0.3);

            box-shadow: none;
        }

        .btn.secondary:hover {
            background: white;
            color: #071b3a;
        }

        /* =========================
           SECTIONS
        ========================= */

        section {
            padding: 90px 7%;
        }

        .section-title {
            text-align: center;
            margin-bottom: 55px;
        }

        .section-title h2 {
            color: #071b3a;

            font-size: 38px;

            margin-bottom: 12px;
        }

        .section-title h2::after {
            content: "";

            display: block;

            width: 60px;
            height: 4px;

            background: #0d6efd;

            border-radius: 10px;

            margin: 15px auto 0;
        }

        .section-title p {
            color: #697586;
            font-size: 17px;
        }

        /* =========================
           ABOUT
        ========================= */

        .about {
            background: white;
        }

        .about-grid {
            max-width: 1150px;

            margin: auto;

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 60px;

            align-items: center;
        }

        .about-image {
            position: relative;
        }

        .about-image img {
            width: 100%;

            height: 400px;

            object-fit: cover;

            border-radius: 25px;

            box-shadow:
                0 25px 60px rgba(7,27,58,0.18);

            transition: 0.5s;
        }

        .about-image img:hover {
            transform: scale(1.03);
        }

        .about-image::after {
            content: "";

            position: absolute;

            width: 100%;
            height: 100%;

            border: 3px solid #0d6efd;

            border-radius: 25px;

            left: 15px;
            top: 15px;

            z-index: -1;
        }

        .about-content h2 {
            color: #071b3a;

            font-size: 35px;

            margin-bottom: 20px;
        }

        .about-content p {
            color: #657184;

            margin-bottom: 18px;

            line-height: 1.8;
        }

        .about-content .btn {
            margin-top: 15px;
        }

        /* =========================
           FEATURES
        ========================= */

        .features {
            background: #f4f7fb;
        }

        .feature-grid {
            max-width: 1200px;

            margin: auto;

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 25px;
        }

        .feature-card {
            background: white;

            padding: 35px 28px;

            border-radius: 20px;

            text-align: center;

            border: 1px solid #e7edf5;

            box-shadow:
                0 10px 35px rgba(20,50,90,0.07);

            transition: 0.35s;

            position: relative;

            overflow: hidden;
        }

        .feature-card::before {
            content: "";

            position: absolute;

            top: 0;
            left: 0;

            width: 100%;
            height: 4px;

            background:
                linear-gradient(
                    90deg,
                    #0d6efd,
                    #4da3ff
                );

            transform: scaleX(0);

            transform-origin: left;

            transition: 0.4s;
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card:hover {
            transform: translateY(-10px);

            box-shadow:
                0 20px 45px rgba(13,110,253,0.15);
        }

        .feature-icon {
            width: 70px;
            height: 70px;

            margin: 0 auto 20px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 18px;

            background: #eaf3ff;

            color: #0d6efd;

            font-size: 27px;

            transition: 0.4s;
        }

        .feature-card:hover .feature-icon {
            background: #0d6efd;
            color: white;

            transform: rotateY(180deg);
        }

        .feature-card h3 {
            color: #14213d;

            margin-bottom: 12px;
        }

        .feature-card p {
            color: #6c7787;

            font-size: 15px;
        }

        /* =========================
           HOW IT WORKS
        ========================= */

        .how-section {
            background: white;
        }

        .steps {
            max-width: 1100px;

            margin: auto;

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 25px;
        }

        .step {
            text-align: center;

            padding: 30px 20px;

            transition: 0.3s;
        }

        .step:hover {
            transform: translateY(-8px);
        }

        .step-number {
            width: 55px;
            height: 55px;

            margin: 0 auto 20px;

            border-radius: 50%;

            display: flex;

            align-items: center;
            justify-content: center;

            background: #0d6efd;

            color: white;

            font-weight: bold;

            box-shadow:
                0 10px 25px rgba(13,110,253,0.3);
        }

        .step > i {
            font-size: 30px;

            color: #0d6efd;

            margin-bottom: 15px;
        }

        .step h3 {
            color: #071b3a;

            margin-bottom: 8px;
        }

        .step p {
            color: #6c7787;

            font-size: 14px;
        }

        /* =========================
           CTA
        ========================= */

        .cta {
            text-align: center;

            background:
                linear-gradient(
                    135deg,
                    #071b3a,
                    #0d47a1
                );

            color: white;

            position: relative;

            overflow: hidden;
        }

        .cta h2 {
            font-size: 40px;

            margin-bottom: 12px;
        }

        .cta p {
            color: #cbdcf5;

            margin-bottom: 25px;
        }

        .cta .btn {
            background: white;
            color: #0d47a1;
        }

        /* =========================
           FOOTER
        ========================= */

        footer {
            background: #041126;

            color: white;

            padding: 60px 7% 20px;
        }

        .footer-content {
            max-width: 1200px;

            margin: auto;

            display: flex;

            justify-content: space-between;

            gap: 40px;

            padding-bottom: 35px;

            border-bottom:
                1px solid rgba(255,255,255,0.1);
        }

        .footer-content h2 {
            margin-bottom: 10px;
        }

        .footer-content h2 i {
            color: #4da3ff;
        }

        .footer-content p {
            color: #aebbd0;
        }

        .footer-links {
            display: flex;

            gap: 20px;

            align-items: center;
        }

        .footer-links a {
            color: #cbd7e8;

            text-decoration: none;

            transition: 0.3s;
        }

        .footer-links a:hover {
            color: #4da3ff;
        }

        .copyright {
            text-align: center;

            padding-top: 25px;

            color: #8795aa;

            font-size: 14px;
        }

        /* =========================
           ANIMATIONS
        ========================= */

        @keyframes fadeUp {

            from {
                opacity: 0;
                transform: translateY(35px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }

        }

        @keyframes floating {

            0%, 100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(20px);
            }

        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1000px) {

            .feature-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .steps {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 800px) {

            .navbar {
                flex-direction: column;

                gap: 12px;
            }

            .about-grid {
                grid-template-columns: 1fr;
            }

            .about-image img {
                height: 320px;
            }

            .footer-content {
                flex-direction: column;

                text-align: center;
            }

            .footer-links {
                justify-content: center;

                flex-wrap: wrap;
            }

        }

        @media (max-width: 600px) {

            .navbar nav a {
                padding: 8px;

                font-size: 14px;
            }

            .navbar nav a i {
                display: none;
            }

            .hero {
                min-height: 600px;
            }

            .hero h1 {
                font-size: 42px;
            }

            .hero p {
                font-size: 16px;
            }

            section {
                padding: 65px 5%;
            }

            .section-title h2 {
                font-size: 30px;
            }

            .feature-grid,
            .steps {
                grid-template-columns: 1fr;
            }

            .cta h2 {
                font-size: 30px;
            }

        }

    </style>

</head>

<body>

<!-- =========================
     NAVBAR
========================= -->

<header class="navbar">

    <div class="logo">
        <i class="fas fa-book-open"></i>
        Library Management
    </div>

    <nav>

        <a href="index.php">
            <i class="fas fa-house"></i>
            Home
        </a>

        <a href="login.php">
            <i class="fas fa-right-to-bracket"></i>
            Login
        </a>

        <a href="register.php">
            <i class="fas fa-user-shield"></i>
            Admin Register
        </a>

    </nav>

</header>


<!-- =========================
     HERO
========================= -->

<section class="hero">

    <div class="hero-content">

        <div class="badge">

            <i class="fas fa-book"></i>

            Welcome to Our Digital Library

        </div>

        <h1>

            Library

            <span>Management System</span>

        </h1>

        <p>

            Manage students, books, issuing and returning
            through one simple, smart and modern system.

        </p>

        <div class="hero-buttons">

            <a href="login.php" class="btn">

                <i class="fas fa-right-to-bracket"></i>

                Login

            </a>

            <a href="register.php"
               class="btn secondary">

                <i class="fas fa-user-shield"></i>

                Admin Register

            </a>

        </div>

    </div>

</section>


<!-- =========================
     ABOUT
========================= -->

<section class="about">

    <div class="section-title">

        <h2>About Our Library</h2>

        <p>
            A modern solution for simple and efficient
            library management.
        </p>

    </div>


    <div class="about-grid">

        <div class="about-image">

            <img
            src="https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=900&q=85"
            alt="Modern Library">

        </div>


        <div class="about-content">

            <h2>
                Smart & Easy Library Management
            </h2>

            <p>

                Our Library Management System helps
                administrators manage students and books
                in an organized and efficient way.

            </p>

            <p>

                Admin can create student accounts,
                add books, issue books and manage
                returned books.

            </p>

            <a href="login.php" class="btn">

                Get Started

                <i class="fas fa-arrow-right"></i>

            </a>

        </div>

    </div>

</section>


<!-- =========================
     FEATURES
========================= -->

<section class="features">

    <div class="section-title">

        <h2>Powerful Features</h2>

        <p>
            Everything you need to manage your library.
        </p>

    </div>


    <div class="feature-grid">


        <div class="feature-card">

            <div class="feature-icon">

                <i class="fas fa-users"></i>

            </div>

            <h3>
                Student Management
            </h3>

            <p>

                Admin can create and manage
                student accounts with unique IDs.

            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">

                <i class="fas fa-book"></i>

            </div>

            <h3>
                Book Management
            </h3>

            <p>

                Add, view and manage all books
                available in the library.

            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">

                <i class="fas fa-book-open"></i>

            </div>

            <h3>
                Issue Books
            </h3>

            <p>

                Easily issue available books
                to registered students.

            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">

                <i class="fas fa-rotate-left"></i>

            </div>

            <h3>
                Return Books
            </h3>

            <p>

                Manage returned books and
                automatically update quantity.

            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">

                <i class="fas fa-shield-halved"></i>

            </div>

            <h3>
                Secure Login
            </h3>

            <p>

                Separate secure access for
                administrators and students.

            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">

                <i class="fas fa-database"></i>

            </div>

            <h3>
                MySQL Database
            </h3>

            <p>

                Store student, book and issue
                records in MySQL database.

            </p>

        </div>


    </div>

</section>


<!-- =========================
     HOW IT WORKS
========================= -->

<section class="how-section">

    <div class="section-title">

        <h2>How It Works</h2>

        <p>
            Manage your library in four simple steps.
        </p>

    </div>


    <div class="steps">


        <div class="step">

            <div class="step-number">
                01
            </div>

            <i class="fas fa-user-shield"></i>

            <h3>
                Admin Login
            </h3>

            <p>
                Admin logs into the library
                management system.
            </p>

        </div>


        <div class="step">

            <div class="step-number">
                02
            </div>

            <i class="fas fa-user-plus"></i>

            <h3>
                Add Student
            </h3>

            <p>
                Admin creates student accounts
                with unique Student IDs.
            </p>

        </div>


        <div class="step">

            <div class="step-number">
                03
            </div>

            <i class="fas fa-book"></i>

            <h3>
                Add Books
            </h3>

            <p>
                Add and manage books available
                in the library.
            </p>

        </div>


        <div class="step">

            <div class="step-number">
                04
            </div>

            <i class="fas fa-hand-holding-heart"></i>

            <h3>
                Issue & Return
            </h3>

            <p>
                Issue books and manage their
                return easily.
            </p>

        </div>


    </div>

</section>


<!-- =========================
     CTA
========================= -->

<section class="cta">

    <h2>
        Ready to Manage Your Library?
    </h2>

    <p>
        Login now and start managing your library.
    </p>

    <a href="login.php" class="btn">

        Login Now

        <i class="fas fa-arrow-right"></i>

    </a>

</section>


<!-- =========================
     FOOTER
========================= -->

<footer>

    <div class="footer-content">

        <div>

            <h2>

                <i class="fas fa-book-open"></i>

                Library Management System

            </h2>

            <p>

                Smart, simple and efficient
                library management.

            </p>

        </div>


        <div class="footer-links">

            <a href="index.php">
                Home
            </a>

            <a href="login.php">
                Login
            </a>

            <a href="register.php">
                Admin Register
            </a>

        </div>

    </div>


    <div class="copyright">

        © 2026 Library Management System.
        All Rights Reserved.

    </div>

</footer>


<!-- =========================
     JAVASCRIPT
========================= -->

<script>

    // Smooth reveal animation
    const cards = document.querySelectorAll(
        ".feature-card, .step, .about-content"
    );

    const observer = new IntersectionObserver(
        (entries) => {

            entries.forEach((entry) => {

                if (entry.isIntersecting) {

                    entry.target.style.opacity = "1";
                    entry.target.style.transform = "translateY(0)";

                }

            });

        },
        {
            threshold: 0.15
        }
    );


    cards.forEach((card) => {

        card.style.opacity = "0";

        card.style.transform = "translateY(30px)";

        card.style.transition = "0.7s ease";

        observer.observe(card);

    });


    // Button click effect
    document.querySelectorAll(".btn").forEach((button) => {

        button.addEventListener("click", function() {

            this.style.transform = "scale(0.96)";

            setTimeout(() => {

                this.style.transform = "";

            }, 150);

        });

    });

</script>

</body>

</html>