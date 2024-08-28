<?php
$server = "localhost";
$username = "root";
$password = "";
$dbname = "qrcodedb";

$conn = new mysqli($server, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['date'])) {
    $date = $conn->real_escape_string($_GET['date']);

    $query = "SELECT YEAR_SECTION, STUDENTID, STUDENTNAME, TIMEIN, TIMEOUT, ATTENDANCE
              FROM table_attendance
              WHERE DATE(TIMEIN) = '$date'";
    $result = $conn->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['YEAR_SECTION']) . "</td>";
            echo "<td>" . htmlspecialchars($row['STUDENTID']) . "</td>";
            echo "<td>" . htmlspecialchars($row['STUDENTNAME']) . "</td>";
            echo "<td>" . htmlspecialchars($row['TIMEIN']) . "</td>";
            echo "<td>" . htmlspecialchars($row['TIMEOUT']) . "</td>";
            echo "<td>" . htmlspecialchars($row['ATTENDANCE']) . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No records found.</td></tr>";
    }
} else {
    echo "<tr><td colspan='5'>No date specified.</td></tr>";
}

$conn->close();
?>
