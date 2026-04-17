<?php
session_start();
require 'config.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $q = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    if (mysqli_num_rows($q) > 0) {
        $user = mysqli_fetch_assoc($q);
        $_SESSION['user'] = $user;
        if ($user['role'] == 'Admin') header("Location: admin/dashboard.php");
        elseif ($user['role'] == 'User') header("Location: user/dashboard.php");
        elseif ($user['role'] == 'Keuangan') header("Location: keuangan/dashboard.php");
    } else {
        $error = "Username atau password salah";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Monitoring Kendaraan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* Animasi fade-in + slide-up */
        @keyframes fadeSlideUp {
            0% { opacity: 0; transform: translateY(40px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .login-box {
            background: #007bff; /* Biru */
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 370px;
            text-align: center;
            box-shadow: 0px 4px 12px rgba(0,0,0,0.25);
            color: white;
            animation: fadeSlideUp 0.8s ease; /* efek saat muncul */
        }

        .login-box img {
            width: 140px;   /* diperbesar sedikit */
            height: auto;
            margin-bottom: 20px;
        }

        .login-box h2 {
            margin-bottom: 20px;
        }

        .login-box input {
            width: 90%;
            padding: 10px;
            margin: 8px 0;
            border: none;
            border-radius: 5px;
            outline: none;
        }

        .login-box input:focus {
            box-shadow: 0 0 8px rgba(255,255,255,0.8);
        }

        .login-box button {
            width: 95%;
            padding: 10px;
            margin-top: 15px;
            border: none;
            border-radius: 5px;
            background: #0056b3;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .login-box button:hover {
            background: #004494;
        }

        .error {
            color: yellow;
            margin-bottom: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <img src="logo pm.png" alt="Logo"> <!-- ganti dengan logo Anda -->
        <h2>Login</h2>
        <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit" name="login">Login</button>
        </form>
    </div>
</body>
</html>
