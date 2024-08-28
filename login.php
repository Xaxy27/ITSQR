<?php
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: index.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "qrcodedb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $input_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($input_password, $user['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $input_username;
            header("Location: index.php");
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with that username.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <title>Login</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(to right, #004d00, #b3ff66, #000000);
            background-size: cover;
            background-attachment: fixed;
        }
        .navbar {
            background-color: #343a40;
            padding: 15px 20px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            color: #ffffff;
            text-decoration: none;
            font-size: 1.25rem;
        }
        .navbar-brand img {
            margin-right: 15px;
        }
        .navbar-brand h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: bold;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            margin-top: 80px;
        }
        .login-container h2 {
            margin-bottom: 20px;
        }
        .input-group {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .input-group i {
            padding: 10px;
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            border-radius: 0.25rem 0 0 0.25rem;
            color: #495057;
        }
        .input-group input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 0 0.25rem 0.25rem 0;
            margin-left: -1px;
        }

    </style>
</head>
<body>
<nav class="navbar">
        <div class="navbar-brand">
        <img src="img/BSIT.png" width="50" height="50" class="d-inline-block align-top" alt="Logo">
            <h1>ITS Attendance System</h1>
        </div>
    </nav>
<div class="login-container" style="margin-bottom:140px;">
        <h2 style="text-align:center;">Login</h2>
        <form method="POST" action="">
            <div class="input-group">
                <i class="fa fa-user"></i>
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
            </div>
            <div class="input-group">
            <a href="update_password.php" class="your-class-here">
    <i class="fa fa-lock"></i>
</a>
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            </div>
            <center><button type="submit" class="btn btn-primary">Login</button></center>
        </form>
    </div>

    <div class="footer">
    <p>&copy; <?php echo date('Y'); ?> ITS Attendance System. All rights reserved.</p>
</div>
</body>
</html>
