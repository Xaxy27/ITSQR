<?php
session_start();
$server = "localhost";
$username = "root";
$password = "";
$dbname = "qrcodedb";

$conn = new mysqli($server, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

if (isset($_POST['text'])) {
    $text = $_POST['text'];
    $date = date('Y-m-d');

    list($studentId, $studentName, $yearSection) = explode(',', $text, 3);

    $sql = "SELECT * FROM table_attendance WHERE STUDENTID=? AND LOGDATE=? AND STATUS='0'";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $studentId, $date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $update_sql = "UPDATE table_attendance SET TIMEOUT=NOW(), STATUS='1' WHERE STUDENTID=? AND LOGDATE=? AND STATUS='0'";
            if ($update_stmt = $conn->prepare($update_sql)) {
                $update_stmt->bind_param("ss", $studentId, $date);
                if ($update_stmt->execute()) {
                    $_SESSION['success'] = "$studentName Has Successfully checked out!";
                } else {
                    $_SESSION['error'] = $update_stmt->error;
                }
                $update_stmt->close();
            } else {
                $_SESSION['error'] = $conn->error;
            }
        } else {
            $insert_sql = "INSERT INTO table_attendance (STUDENTID, STUDENTNAME, YEAR_SECTION, TIMEIN, LOGDATE, STATUS, EVENT, ATTENDANCE, event_id) VALUES (?, ?, ?, NOW(), ?, '0', 'Event1', 'On Time', NULL)";
            if ($insert_stmt = $conn->prepare($insert_sql)) {
                $insert_stmt->bind_param("ssss", $studentId, $studentName, $yearSection, $date);
                if ($insert_stmt->execute()) {
                    $_SESSION['success'] = "Attendance for $studentName has been successfully added!";
                } else {
                    $_SESSION['error'] = $insert_stmt->error;
                }
                $insert_stmt->close();
            } else {
                $_SESSION['error'] = $conn->error;
            }
        }
        
        $stmt->close();
    } else {
        $_SESSION['error'] = $conn->error;
    }

    $conn->close();
    header("Location: index.php");
    exit();
}