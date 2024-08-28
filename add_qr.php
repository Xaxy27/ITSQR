<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "qrcodedb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = $conn->real_escape_string($_POST['student_id']);
    $studentName = $conn->real_escape_string($_POST['student_name']);
    $yearSection = $conn->real_escape_string($_POST['year_section']);

    $sql = "INSERT INTO addqr (student_id, student_name, year_section) VALUES ('$studentId', '$studentName', '$yearSection')";

    if ($conn->query($sql) === TRUE) {
        echo "QR data added successfully.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
