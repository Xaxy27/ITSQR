<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About ITS Attendance</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <script type="text/javascript" src="js/adapter.min.js"></script>
    <script type="text/javascript" src="js/vue.min.js"></script>
    <script type="text/javascript" src="js/instascan.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #004d00, #b3ff66, #000000);
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: #333;
        }
        .content-container {
            background-color: rgba(255, 255, 255, 0.5);
            padding: 20px;
            margin: 20px auto;
            border-radius: 10px;
            max-width: 800px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .content-container h2 {
            font-size: 2em;
        }
        .content-container p {
            font-size: 1em;
        }
        .faculty-images, .people-images {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .faculty-images .faculty-member, .people-images .people-member {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 10px;
        }
        .faculty-images img, .people-images img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin-bottom: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .faculty-images span, .people-images span {
            font-size: 1em;
            font-weight: bold;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-light fixed-top">
    <div class="navbar-brand">
        <img src="img/BSIT.png" width="50" height="50" class="d-inline-block align-top" alt="Logo">
        <h1>ITS Attendance System</h1>
        <div class="navbar-nav ml-auto">
            <a class="nav-item nav-link" href="index.php" style="color: white; margin-left:30px;">Home</a>
            <a class="nav-item nav-link" href="generate.php" style="color: white; margin-left:30px;">Generate QR</a>
            <a class="nav-item nav-link" href="upload_qr.php" style="color: white; margin-left:30px;">Upload QR/Excel</a>
            <a class="nav-item nav-link" href="history.php" style="color: white; margin-left:30px;">Records</a>
            <a class="nav-item nav-link" href="about.php" style="color: white; margin-left:30px;">About</a>
            <a class="nav-item nav-link" href="logout.php" style="color: white; margin-left:30px;">Logout</a>
        </div>
    </div>
</nav>

<div class="content-container">
    <h2>About ITS Attendance System</h2>
    <p>As members of the IT society committed to innovation and progress,
       we have chosen to implement a cutting-edge QR registration system to enhance our events and activities. 
       Each enrolled student will be assigned a unique QR code linked to our student database. 
       By scanning these QR codes during event registration, we will seamlessly capture and record attendance, 
       streamlining the process and improving accuracy.</p>
</div>

<div class="content-container">
    <h2>IT Faculty</h2>
    <div class="faculty-images">
        <div class="faculty-member">
            <img src="faculty/ms_a.png" alt="Faculty 1">
            <span>Mrs. Almenda Asuncion</span>
        </div>
        <div class="faculty-member">
            <img src="faculty/sir_tan.png" alt="Faculty 2">
            <span>Dr. Riegie D. Tan</span>
        </div>
        <div class="faculty-member">
            <img src="img/PCC.png" alt="Faculty 3">
            <span>Mr. Pepito Raviz</span>
        </div>
        <div class="faculty-member">
            <img src="faculty/sir_june.png" alt="Faculty 4">
            <span>Mr. Claudio Charanguero</span>
        </div>
    </div>
</div>

<div class="content-container">
    <h2>People Behind The System</h2>
    <p>These are the people who make this system possible.</p>
    <div class="people-images">
        <div class="people-member">
            <img src="developers/ssob_ednis.png" alt="Person 1">
            <span>Dennis Catacutan</span>
            <span style="margin-top:5px;">ITS PRESIDENT</span>
        </div>
        <div class="people-member">
            <img src="developers/hazel.png" alt="Person 2">
            <span>Hazel Rhea Ferido</span>
            <span style="margin-top:5px;">ITS VICE PRESIDENT</span>
        </div>
        <div class="people-member">
            <img src="developers/koya_pol.png" alt="Person 3">
            <span>Paul Gerald Lopez</span>
            <span style="margin-top:5px;">ITS BUSINESS MANAGER</span>
        </div>
        <div class="people-member">
            <img src="developers/chanie.png" alt="Person 4">
            <span>Simone Zack Bayug</span>
            <span style="margin-top:5px;">ITS PRO EXTERNAL</span>
        </div>
        <div class="people-member">
            <img src="developers/dustin.png" alt="Person 5">
            <span>Dustin Reyes</span>
            <span style="margin-top:5px;">ITS CREATIVES 3</span>
        </div>
        <div class="people-member">
            <img src="developers/xyrus_pogi.png" alt="Person 6">
            <span>Xyrus Guillergan</span>
            <span style="margin-top:5px;">ITS CREATIVES 1</span>
        </div>
        <div class="people-member">
            <img src="developers/cyrill.png" alt="Person 7">
            <span>Cyril Mel Macapugas</span>
            <span style="margin-top:5px;">ITS 4TH YEAR BEADLE</span>
        </div>
    </div>
</div>

<div class="space" style="padding-bottom:50px;">
    <div>
<div class="footer">
    <p>&copy; <?php echo date('Y'); ?> ITS Attendance System. All rights reserved.</p>
</div>
</body>
</html>
