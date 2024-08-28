<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$server = "localhost";
$username = "root";
$password = "";
$dbname = "qrcodedb";

$conn = new mysqli($server, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['event_date']) && isset($_POST['event_name'])) {
    $event_date = $conn->real_escape_string($_POST['event_date']);
    $event_name = $conn->real_escape_string($_POST['event_name']);

    $query = "INSERT INTO events (event_name, event_date) VALUES ('$event_name', '$event_date') ON DUPLICATE KEY UPDATE event_name = VALUES(event_name)";
    if ($conn->query($query) === TRUE) {
        $_SESSION['success'] = "Event updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating event: " . $conn->error;
    }
}

$query = "SELECT DISTINCT DATE(TIMEIN) AS date, e.event_name FROM table_attendance t
          LEFT JOIN events e ON e.event_date = DATE(t.TIMEIN)
          ORDER BY date DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <title>Attendance History</title>
    <link rel="stylesheet" href="css/style.css">
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

    .details-container {
        display: none;
        margin-top: 10px;
        background-color: white;
        padding: 10px;
        border: 1px solid #dee2e6;
    }

    .table-custom {
        background-color: white;
        border: 1px solid #dee2e6;
    }

    .table-custom th, .table-custom td {
        border: 1px solid #dee2e6;
        padding: 8px;
    }

    .table-custom thead {
        background-color: #f8f9fa;
    }

    .table-custom tbody tr {
        background-color: white;
    }

    .table-custom tbody tr:hover {
        background-color: #f2f2f2;
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

<div class="container mt-4">
    <h3 style="color:white;">Attendance History</h3>
    <form method="POST" class="form-inline mb-3" style="margin-bottom:20px;">
        <div class="form-group mr-2">
            <input type="date" name="event_date" class="form-control mr-2" placeholder="Event Date" required>
        </div>
        <div class="form-group mr-2">
            <input type="text" name="event_name" class="form-control mr-2" placeholder="Event Name" required>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Event
        </button>
    </form>
    <table class="table table-bordered table-custom">
        <thead>
            <tr>
                <th>Date</th>
                <th>Event</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($row['date']))); ?></td>
                <td>
                    <form method="POST" class="form-inline">
                        <input type="hidden" name="event_date" value="<?php echo htmlspecialchars($row['date']); ?>">
                        <input type="text" name="event_name" value="<?php echo htmlspecialchars($row['event_name']); ?>" class="form-control mr-2" placeholder="Event Name">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save"></i> Save
                        </button>
                    </form>
                </td>
                <td>
                    <button class="btn btn-primary btn-sm view-records" data-date="<?php echo htmlspecialchars($row['date']); ?>">
                    <i class="fa fa-file-text" aria-hidden="true"></i> View Records
                    </button>
                    <a href="export_history.php?date=<?php echo htmlspecialchars($row['date']); ?>" class="btn btn-success btn-sm">
                    <i class="fa fa-file-text" aria-hidden="true"></i> Export To Excel
                    </a>
                </td>
            </tr>
            <tr class="details-container" id="details-<?php echo htmlspecialchars($row['date']); ?>">
                <td colspan="3">
                    <table class="table table-bordered table-custom">
                        <thead>
                            <tr>
                                <th>YEAR & SECTION</th>
                                <th>STUDENT ID</th>
                                <th>STUDENT NAME</th>
                                <th>TIME IN</th>
                                <th>TIME OUT</th>
                                <th>ATTENDANCE</th>
                            </tr>
                        </thead>
                        <tbody class="records-body">
    <?php
    $recordQuery = "SELECT * FROM table_attendance WHERE DATE(TIMEIN) = '" . $conn->real_escape_string($row['date']) . "'";
    $recordResult = $conn->query($recordQuery);
    while ($record = $recordResult->fetch_assoc()) {
        $timein = !empty($record['TIMEIN']) ? date('g:i A', strtotime($record['TIMEIN'])) : '';
        $timeout = !empty($record['TIMEOUT']) ? date('g:i A', strtotime($record['TIMEOUT'])) : '';
    ?>
    <tr>
        <td><?php echo htmlspecialchars($record['YEAR_SECTION']); ?></td>
        <td><?php echo htmlspecialchars($record['STUDENTID']); ?></td>
        <td><?php echo htmlspecialchars($record['STUDENTNAME']); ?></td>
        <td><?php echo htmlspecialchars($timein); ?></td>
        <td><?php echo htmlspecialchars($timeout); ?></td>
        <td><?php echo htmlspecialchars($record['ATTENDANCE']); ?></td>
    </tr>
    <?php
    }
    ?>
</tbody>

                    </table>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="space" style="padding-bottom:50px;">
    <div>
<div class="footer">
    <p>&copy; <?php echo date('Y'); ?> ITS Attendance System. All rights reserved.</p>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.6/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('.view-records').on('click', function() {
            var button = $(this);
            var date = button.data('date');
            var detailsContainer = $('#details-' + date);
            var recordsBody = detailsContainer.find('.records-body');

            if (detailsContainer.is(':visible')) {
                detailsContainer.hide();
                recordsBody.empty();
            } else {
                $.ajax({
                    url: 'fetch_records.php',
                    type: 'GET',
                    data: { date: date },
                    success: function(data) {
                        recordsBody.html(data);
                        detailsContainer.show();
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching records:", error);
                        recordsBody.html("<tr><td colspan='6'>Failed to load records.</td></tr>");
                        detailsContainer.show();
                    }
                });
            }
        });
    });
</script>

</body>
</html>

<?php
$conn->close();
?>
