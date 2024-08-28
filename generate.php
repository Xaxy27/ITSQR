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
    <title>QR Code Generator</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/generate.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
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
        .qr-container {
            background-image: url('img/BSIT.png');
            background-size: 70% auto;
            background-position: center;
            background-repeat: no-repeat;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 90%;
            margin-top: 50px;
        }
        .qr-container input, .qr-container button {
            margin: 10px 0;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark fixed-top">
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

<div class="container qr-container">
    <h2>ITS QR Code Generator</h2>
    <canvas id="qr-canvas" style="display: none;"></canvas>
    <img id="qr-code" style="max-width: 100%; height: auto;">
    <input type="text" id="student-id" placeholder="Enter Student ID">
    <input type="text" id="student-name" placeholder="Enter Student Name">
    <input type="text" id="year-section" placeholder="Enter Year and Section">
    <button id="generate-btn"><i class="fa fa-qrcode"></i> Generate</button>    
   <button id="add-qr-btn"><i class="fa fa-plus"></i> Add QR</button>
    <button id="download-btn"><i class="fa fa-download"></i> Download</button>
</div>

<div class="footer">
    <p>&copy; <?php echo date('Y'); ?> ITS Attendance System. All rights reserved.</p>
</div>

<script>
    const qrCanvas = document.getElementById("qr-canvas"),
          qrImage = document.getElementById("qr-code"),
          studentIdInput = document.getElementById("student-id"),
          studentNameInput = document.getElementById("student-name"),
          yearSectionInput = document.getElementById("year-section"),
          generateBtn = document.getElementById("generate-btn"),
          downloadBtn = document.getElementById("download-btn"),
          api = 'https://api.qrserver.com/v1/',
          api2 = 'create-qr-code/?size=150x150&data=';

    generateBtn.addEventListener("click", () => {
        const studentId = studentIdInput.value.trim();
        const studentName = studentNameInput.value.trim();
        const yearSection = yearSectionInput.value.trim();

        if (studentId && studentName && yearSection) {
            const qrValue = `${studentId},${studentName},${yearSection}`;
            const qrUrl = `${api}${api2}${encodeURIComponent(qrValue)}`;

            const qrImageElement = new Image();
            qrImageElement.crossOrigin = "Anonymous";
            qrImageElement.onload = () => {
                const ctx = qrCanvas.getContext("2d");
                qrCanvas.width = qrImageElement.width;
                qrCanvas.height = qrImageElement.height + 20;
                ctx.clearRect(0, 0, qrCanvas.width, qrCanvas.height);

                ctx.fillStyle = "#fff";
                ctx.fillRect(0, 0, qrCanvas.width, qrCanvas.height);

                ctx.drawImage(qrImageElement, 0, 0);
                ctx.font = "16px Arial";
                ctx.textAlign = "center";
                ctx.fillStyle = "#000";
                ctx.fillText(studentId, qrCanvas.width / 2, qrImageElement.height + 15);

                qrImage.src = qrCanvas.toDataURL();
            };
            qrImageElement.src = qrUrl;
        } else {
            alert("Please enter Student ID, Student Name, and Year and Section.");
        }
    });

    downloadBtn.addEventListener("click", () => {
        if (qrImage.src) {
            const studentId = studentIdInput.value.trim();
            const studentName = studentNameInput.value.trim();
            const yearSection = yearSectionInput.value.trim();
            const qrValue = (studentId || 'qr-code') + '-' + (studentName || '') + '-' + (yearSection || '');
            const dataUrl = qrCanvas.toDataURL("image/png");

            const link = document.createElement("a");
            link.href = dataUrl;
            link.download = `${qrValue}.png`;
            link.click();
        } else {
            alert("Please generate a QR code first.");
        }
    });
    const addQrBtn = document.getElementById("add-qr-btn");

addQrBtn.addEventListener("click", () => {
    const studentId = studentIdInput.value.trim();
    const studentName = studentNameInput.value.trim();
    const yearSection = yearSectionInput.value.trim();

    if (studentId && studentName && yearSection) {
        fetch('add_qr.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                'student_id': studentId,
                'student_name': studentName,
                'year_section': yearSection
            })
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            window.location.href = `view_qr.php`;
        })
        .catch(error => {
            console.error('Error:', error);
        });
    } else {
        alert("Please enter Student ID, Student Name, and Year and Section.");
    }
});

</script>
</body>
</html>
