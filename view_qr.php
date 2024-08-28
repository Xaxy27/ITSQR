<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
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

$sql = "SELECT * FROM addqr";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View QR Codes</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        .container {
            margin-top: 50px;
        }
        .table-container {
            background-color: gray;
            background-size: 70% auto;
            background-position: center;
            background-repeat: no-repeat;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
            max-width: 800px;
            text-align: center;
        }
        .table-container img {
            max-width: 110px;
            height: auto;
        }
        .btn-download {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container table-container">
        <h2>QR Codes and Student Details</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>QR Code</th>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>Year & Section</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $qrValue = $row['student_id'] . ',' . $row['student_name'] . ',' . $row['year_section'];
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrValue);
$fileName = strtoupper($row['student_id']) . '-' . strtoupper($row['student_name']) . '-' . strtoupper($row['year_section']) . '.png';
echo "<td><img src='$qrUrl' alt='QR Code'></td>";
echo "<td>" . htmlspecialchars($row['student_id']) . "</td>";
echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
echo "<td>" . htmlspecialchars($row['year_section']) . "</td>";
echo "<td><a href='download_qr.php?url=" . urlencode($qrUrl) . "&filename=" . urlencode($fileName) . "' class='btn btn-primary btn-download'><i class='fa fa-download'></i> Download</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <a href="generate.php" class="btn btn-primary">Back to Generator</a>
    </div>
    <?php $conn->close(); ?>
</body>
</html>
