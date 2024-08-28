<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $fileName = $_FILES['excel_file']['tmp_name'];
    if ($_FILES['excel_file']['size'] > 0) {
        $spreadsheet = IOFactory::load($fileName);
        $sheetData = $spreadsheet->getActiveSheet()->toArray();

        $server = "localhost";
        $username = "root";
        $password = "";
        $dbname = "qrcodedb";
        $conn = new mysqli($server, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");

        for ($i = 1; $i < count($sheetData); $i++) {
            $row = $sheetData[$i];
            $year_section = $conn->real_escape_string($row[0]);
            $student_id = $conn->real_escape_string($row[1]);
            $student_name = $conn->real_escape_string($row[2]);
            $time_in = $conn->real_escape_string($row[3]);
            $time_out = !empty($row[4]) ? $conn->real_escape_string($row[4]) : NULL;
            $attendance = $conn->real_escape_string($row[5]);

            $sql = "INSERT INTO table_attendance (YEAR_SECTION, STUDENTID, STUDENTNAME, TIMEIN, TIMEOUT, ATTENDANCE) 
                    VALUES ('$year_section', '$student_id', '$student_name', '$time_in', 
                    " . ($time_out !== NULL ? "'$time_out'" : "NULL") . ", '$attendance')";
            
            $conn->query($sql);
        }

        $conn->close();
        $_SESSION['success'] = "Data imported successfully.";
    } else {
        $_SESSION['error'] = "File is empty.";
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_late'])) {
    $server = "localhost";
    $username = "root";
    $password = "";
    $dbname = "qrcodedb";
    $conn = new mysqli($server, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
    $specialCharacter = 'Mañana'; 
    $id = $conn->real_escape_string($_POST['id']);
    
    $attendance_check_sql = "SELECT ATTENDANCE FROM table_attendance WHERE ID = ?";
    if ($stmt = $conn->prepare($attendance_check_sql)) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($current_attendance);
        $stmt->fetch();
        $stmt->close();
    
        $new_attendance = ($current_attendance == 'Late') ? 'On Time' : 'Late';
        $update_sql = "UPDATE table_attendance SET ATTENDANCE = ? WHERE ID = ?";
        if ($stmt = $conn->prepare($update_sql)) {
            $stmt->bind_param("si", $new_attendance, $id);
            if ($stmt->execute()) {
                $_SESSION['success'] = $new_attendance == 'Late' ? "$studentName marked as late." : "Late mark removed.";
            } else {
                $_SESSION['error'] = "Failed to update attendance.";
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = "Failed to prepare update statement.";
        }
    } else {
        $_SESSION['error'] = "Failed to prepare attendance check statement.";
    }

    $conn->close();
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

        .alert {
            margin-top: 20px;
        }
        .camera-container {
            width: 60%;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #2c3e50;
            border-radius: 10px;
            text-align: center;
        }
        .camera-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 10px;
        }

        .table-container {
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .centered-form {
            display: flex;
            justify-content: center;
            width: 100%;
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
<div class="container mt-5 pt-5">
    <div class="camera-container">
        <h3 style="color:white;">ITS QR Code Scanner</h3>
        <video id="preview" width="80%"></video>
        <div class="camera-controls text-center">
            <button type="button" id="toggle-camera" class="btn btn-primary">
                <i class="fa fa-video-camera" aria-hidden="true"></i> <span id="camera-status">Start</span>
            </button>
        </div>

        <?php
        if (isset($_SESSION['error'])) {
            echo "
            <div id='error-message' class='alert alert-danger'>
            <h4>Error!</h4>
            " . htmlspecialchars($_SESSION['error']) . "
            </div>
            ";
            unset($_SESSION['error']);
        }

        if (isset($_SESSION['success'])) {
            echo "
            <div id='success-message' class='alert alert-success'>
            <h4>Success!</h4>
            " . htmlspecialchars($_SESSION['success']) . "
            </div>
            ";
            unset($_SESSION['success']);
        }
        ?>
    </div>
        
    <div class="table-container">
        <h3>Attendance Records</h3>
        <form action="insert1.php" method="post" class="centered-form form-horizontal">
            <div class="form-group">
                <input type="text" name="text" id="text" style="display: none;" readonly="" placeholder="Scan QR Code" class="form-control">
            </div>
        </form>
       
        <form method="GET" action="index.php" class="centered-form form-inline mb-3">
            <input type="text" name="search" class="form-control mr-2" placeholder="Search by ID or Name" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Search
            </button>
        </form><br>


        <table class="table table-bordered table-custom">
            <thead>
                <tr>
                    <th>YEAR & SECTION</th>
                    <th>STUDENT ID</th>
                    <th>STUDENT NAME</th>
                    <th>TIME IN</th>
                    <th>TIME OUT</th>
                    <th>ATTENDANCE</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $server = "localhost";
            $username = "root";
            $password = "";
            $dbname = "qrcodedb";
            $conn = new mysqli($server, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $conn->set_charset("utf8mb4");

            $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

            $limit = 4;
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $offset = ($page - 1) * $limit;

            $sql = "SELECT ID, YEAR_SECTION, STUDENTID, STUDENTNAME, TIMEIN, TIMEOUT, ATTENDANCE 
                FROM table_attendance 
                WHERE DATE(TIMEIN) = CURDATE()";

            if ($search) {
                $sql .= " AND (ID LIKE '%$search%' OR YEAR_SECTION LIKE '%$search%' OR STUDENTID LIKE '%$search%' OR STUDENTNAME LIKE '%$search%')";
            }

            $sql .= " ORDER BY TIMEIN DESC 
                    LIMIT $limit OFFSET $offset";

            $query = $conn->query($sql);
            while ($row = $query->fetch_assoc()) {
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['YEAR_SECTION']); ?></td>
                        <td><?php echo htmlspecialchars($row['STUDENTID']); ?></td>
                        <td><?php echo htmlspecialchars($row['STUDENTNAME']); ?></td>
                        <td><?php echo htmlspecialchars($row['TIMEIN']); ?></td>
                        <td><?php echo htmlspecialchars($row['TIMEOUT']); ?></td>
                        <td><?php echo htmlspecialchars($row['ATTENDANCE']); ?></td>
                        <td>
                            <form method="post" action="index.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['ID']); ?>">
                                <button type="submit" name="mark_late" class="btn btn-warning btn-sm">
                                    <?php echo $row['ATTENDANCE'] == 'Late' ? 'Remove Late' : 'Mark Late'; ?>
                                </button>
                            </form>
                            <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $row['ID']; ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php
                }

                $count_sql = "SELECT COUNT(*) AS total FROM table_attendance WHERE DATE(TIMEIN) = CURDATE()";
                if ($search) {
                    $count_sql .= " AND (ID LIKE '%$search%' OR YEAR_SECTION LIKE '%$search%' OR STUDENTID LIKE '%$search%' OR STUDENTNAME LIKE '%$search%')";
                }
                $count_query = $conn->query($count_sql);
                $total_records = $count_query->fetch_assoc()['total'];
                $total_pages = ceil($total_records / $limit);
            ?>
            </tbody>
        </table>

        <nav aria-label="Page navigation">
            <ul class="pagination">  
                <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" tabindex="-1">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</div>
<div class="space" style="padding-bottom:50px;">
    <div>
<div class="footer">
    <p>&copy; <?php echo date('Y'); ?> ITS Attendance System. All rights reserved.</p>
</div>

<script>
let scanner;
let isCameraOn = false;

function startCamera() {
    scanner = new Instascan.Scanner({ video: document.getElementById('preview') });
    Instascan.Camera.getCameras().then(function (cameras) {
        if (cameras.length > 0) {
            scanner.start(cameras[0]);
            document.getElementById('toggle-camera').innerHTML = '<i class="fa fa-stop" aria-hidden="true"></i> <span id="camera-status">Stop</span>';
            isCameraOn = true;
        } else {
            alert('No cameras found.');
        }
    }).catch(function (e) {
        console.error(e);
    });

    scanner.addListener('scan', function (c) {
        const decodedData = decodeURIComponent(escape(c));
        console.log(`Scanned QR Code Data: ${decodedData}`);
        document.getElementById('text').value = decodedData;
        document.forms[0].submit();
    });
}

function toggleCamera() {
    const button = document.getElementById('toggle-camera');
    const statusSpan = document.getElementById('camera-status');

    if (isCameraOn) {
        scanner.stop().then(() => {
            button.innerHTML = '<i class="fa fa-video-camera" aria-hidden="true"></i> <span id="camera-status">Start</span>';
            isCameraOn = false;
        }).catch((e) => {
            console.error(e);
        });
    } else {
        startCamera();
    }
}

document.getElementById('toggle-camera').addEventListener('click', toggleCamera);

window.onload = function() {
    startCamera();
    hideMessages();
}

function hideMessages() {
    setTimeout(() => {
        const successMessage = document.getElementById('success-message');
        const errorMessage = document.getElementById('error-message');
        if (successMessage) successMessage.style.display = 'none';
        if (errorMessage) errorMessage.style.display = 'none';
    }, 2000);
}

function confirmDelete(id) {
    var conf = confirm("Are you sure you want to delete this record?");
    if (conf) {
        window.location.href = 'delete.php?id=' + id;
    }
}

</script>
</body>
</html>
