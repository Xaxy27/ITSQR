<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

require 'vendor/autoload.php';

use Zxing\QrReader;
use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['qr_image'])) {
        $fileName = $_FILES['qr_image']['tmp_name'];
        if ($_FILES['qr_image']['size'] > 0) {
            try {
                $qrReader = new QrReader($fileName);
                $decodedData = $qrReader->text();

                if ($decodedData) {
                    list($student_id, $student_name, $year_section) = explode(',', $decodedData);

                    $server = "localhost";
                    $username = "root";
                    $password = "";
                    $dbname = "qrcodedb";
                    $conn = new mysqli($server, $username, $password, $dbname);

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }
                    $conn->set_charset("utf8mb4");

                    $sql_check = "SELECT ID, TIMEIN, TIMEOUT FROM table_attendance 
                                  WHERE STUDENTID = ? AND STUDENTNAME = ? AND YEAR_SECTION = ? 
                                  ORDER BY ID DESC LIMIT 1";
                    $stmt_check = $conn->prepare($sql_check);
                    $stmt_check->bind_param("sss", $student_id, $student_name, $year_section);
                    $stmt_check->execute();
                    $result_check = $stmt_check->get_result();

                    if ($result_check->num_rows > 0) {
                        $row = $result_check->fetch_assoc();

                        if (empty($row['TIMEOUT'])) {
                            $sql_update = "UPDATE table_attendance SET TIMEOUT = NOW() WHERE ID = ?";
                            $stmt_update = $conn->prepare($sql_update);
                            $stmt_update->bind_param("i", $row['ID']);
                            if ($stmt_update->execute()) {
                                $_SESSION['success'] = "Time out recorded successfully.";
                            } else {
                                $_SESSION['error'] = "Failed to record time out.";
                            }
                            $stmt_update->close();
                        } else {
                            $sql_insert = "INSERT INTO table_attendance (STUDENTID, STUDENTNAME, YEAR_SECTION, TIMEIN) VALUES (?, ?, ?, NOW())";
                            $stmt_insert = $conn->prepare($sql_insert);
                            $stmt_insert->bind_param("sss", $student_id, $student_name, $year_section);
                            if ($stmt_insert->execute()) {
                                $_SESSION['success'] = "Time in recorded successfully.";
                            } else {
                                $_SESSION['error'] = "Failed to record time in.";
                            }
                            $stmt_insert->close();
                        }
                    } else {
                        $sql_insert = "INSERT INTO table_attendance (STUDENTID, STUDENTNAME, YEAR_SECTION, TIMEIN) VALUES (?, ?, ?, NOW())";
                        $stmt_insert = $conn->prepare($sql_insert);
                        $stmt_insert->bind_param("sss", $student_id, $student_name, $year_section);
                        if ($stmt_insert->execute()) {
                            $_SESSION['success'] = "Time in recorded successfully.";
                        } else {
                            $_SESSION['error'] = "Failed to record time in.";
                        }
                        $stmt_insert->close();
                    }

                    $stmt_check->close();
                    $conn->close();
                } else {
                    $_SESSION['error'] = "Failed to decode the QR code.";
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Error processing the QR code: " . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = "No file uploaded.";
        }
    }

    if (isset($_FILES['excel_file'])) {
        $fileName = $_FILES['excel_file']['tmp_name'];
        if ($_FILES['excel_file']['size'] > 0) {
            try {
                $spreadsheet = IOFactory::load($fileName);
                $sheet = $spreadsheet->getActiveSheet();
                $data = $sheet->toArray();

                $server = "localhost";
                $username = "root";
                $password = "";
                $dbname = "qrcodedb";
                $conn = new mysqli($server, $username, $password, $dbname);

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }
                $conn->set_charset("utf8mb4");

                $headers = array_shift($data);

                $indexStudentId = array_search('STUDENTID', $headers);
                $indexStudentName = array_search('STUDENTNAME', $headers);
                $indexYearSection = array_search('YEAR_SECTION', $headers);
                $indexAttendance = array_search('ATTENDANCE', $headers);

                foreach ($data as $row) {
                    $student_id = $row[$indexStudentId] ?? '';
                    $student_name = $row[$indexStudentName] ?? '';
                    $year_section = $row[$indexYearSection] ?? '';
                    $attendance = $row[$indexAttendance] ?? '';

                    if ($attendance === 'late') {
                        $sql_check = "SELECT ID, TIMEIN, TIMEOUT FROM table_attendance 
                                      WHERE STUDENTID = ? AND STUDENTNAME = ? AND YEAR_SECTION = ? AND ATTENDANCE = ? 
                                      ORDER BY ID DESC LIMIT 1";
                        $stmt_check = $conn->prepare($sql_check);
                        $stmt_check->bind_param("sss", $student_id, $student_name, $year_section, $attendance);
                        $stmt_check->execute();
                        $result_check = $stmt_check->get_result();

                        if ($result_check->num_rows > 0) {
                            $row_existing = $result_check->fetch_assoc();

                            if (empty($row_existing['TIMEOUT'])) {
                                $sql_update = "UPDATE table_attendance SET TIMEOUT = NOW() WHERE ID = ?";
                                $stmt_update = $conn->prepare($sql_update);
                                $stmt_update->bind_param("i", $row_existing['ID']);
                                if ($stmt_update->execute()) {
                                    $_SESSION['success'] = "Time out recorded successfully.";
                                } else {
                                    $_SESSION['error'] = "Failed to record time out.";
                                }
                                $stmt_update->close();
                            }
                        } else {
                            $sql_insert = "INSERT INTO table_attendance (STUDENTID, STUDENTNAME, YEAR_SECTION, TIMEIN, ATTENDANCE) VALUES (?, ?, ?, NOW(), ?)";
                            $stmt_insert = $conn->prepare($sql_insert);
                            $stmt_insert->bind_param("ssss", $student_id, $student_name, $year_section, $attendance);
                            if ($stmt_insert->execute()) {
                                $_SESSION['success'] = "Time in recorded successfully.";
                            } else {
                                $_SESSION['error'] = "Failed to record time in.";
                            }
                            $stmt_insert->close();
                        }

                        $stmt_check->close();
                    }
                }

                $conn->close();
            } catch (Exception $e) {
                $_SESSION['error'] = "Error processing the Excel file: " . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = "No file uploaded.";
        }
    }

    header("Location: index.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITS Attendance System</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <script type="text/javascript" src="js/adapter.min.js"></script>
    <script type="text/javascript" src="js/vue.min.js"></script>
    <script type="text/javascript" src="js/instascan.min.js"></script>
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
    border: 2px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    background-color: gray;
    margin-top:80px;
    text-align: center;
    color: white;
    width:450px;
}
    </style>
</head>
<body>
    <div class="container">
        <h2>Upload QR Code Image</h2>
        <form action="upload_qr.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="qr_image">Upload QR Code Image:</label>
                <center><input type="file" name="qr_image" id="qr_image" class="form-control-file" required></center>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Upload QR Code</button>
        </form><br>

        <form action="index.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                <h2>Upload Excel File</h2>
                    <label for="excel_file">Import Excel File:</label>
                    <center><input type="file" name="excel_file" id="excel_file" class="form-control-file"></center>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Import Excel</button>
            </form><br style="margin-bottom:5px;">

        <?php
        if (isset($_SESSION['error'])) {
            echo "<div class='alert alert-danger'>" . htmlspecialchars($_SESSION['error']) . "</div>";
            unset($_SESSION['error']);
        }

        if (isset($_SESSION['success'])) {
            echo "<div class='alert alert-success'>" . htmlspecialchars($_SESSION['success']) . "</div>";
            unset($_SESSION['success']);
        }
        ?>
    </div>
    <div class="space" style="padding-bottom:50px;">
    <div>
<div class="footer">
    <p>&copy; <?php echo date('Y'); ?> ITS Attendance System. All rights reserved.</p>
</div>
</body>
</html>