<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "qrcodedb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$plain_password = 'codeblooded';

$hashed_password = password_hash($plain_password, PASSWORD_BCRYPT);

$sql = "UPDATE users SET password = ? WHERE username = 'ITSsociety'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hashed_password);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $_SESSION['success'] = "Password updated successfully.";
} else {
    $_SESSION['error'] = "Password update failed.";
}

$stmt->close();
$conn->close();

header("Location: login.php");
exit();
?>
