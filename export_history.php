<?php
$server = "localhost";
$username = "root";
$password = "";
$dbname = "qrcodedb";

$conn = new mysqli($server, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['date'])) {
    die("No date specified.");
}

$date = $conn->real_escape_string($_GET['date']);

$event_query = "
    SELECT event_name 
    FROM events 
    WHERE event_date = '$date'
";
$event_result = $conn->query($event_query);

if ($event_result && $event_row = $event_result->fetch_assoc()) {
    $event_name = $event_row['event_name'];
} else {
    $event_name = 'No_Event_Name'; 
}

$filename = '' . $event_name . '-' . $date . '.csv';

$query = "SELECT * FROM table_attendance WHERE DATE(TIMEIN) = '$date'";
$result = $conn->query($query);

if ($result) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $file = fopen('php://output', 'w');

    if ($file) {
        fwrite($file, "\xEF\xBB\xBF");

        $headers = array("YEAR & SECTION", "STUDENT ID", "STUDENT NAME", "TIMEIN", "TIMEOUT", "ATTENDANCE");
        fputcsv($file, $headers);

        while ($row = $result->fetch_assoc()) {
            $timeout = ($row['TIMEOUT'] == '0000-00-00 00:00:00') ? '' : $row['TIMEOUT'];

            $data = array(
                mb_convert_encoding($row['YEAR_SECTION'], 'UTF-8', 'auto'),
                " " . mb_convert_encoding($row['STUDENTID'], 'UTF-8', 'auto'),
                mb_convert_encoding($row['STUDENTNAME'], 'UTF-8', 'auto'),
                mb_convert_encoding($row['TIMEIN'], 'UTF-8', 'auto'),
                mb_convert_encoding($timeout, 'UTF-8', 'auto'),
                mb_convert_encoding($row['ATTENDANCE'], 'UTF-8', 'auto')
            );
            fputcsv($file, $data);
        }

        fclose($file);
        exit();
    } else {
        die("Unable to create file.");
    }
} else {
    die("Error retrieving data from the database.");
}

$conn->close();
?>
