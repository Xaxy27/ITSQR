<?php
if (isset($_GET['url']) && isset($_GET['filename'])) {
    $url = $_GET['url'];
    $filename = $_GET['filename'];

    // Validate URL
    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
        echo "Invalid URL.";
        exit();
    }

    // Fetch the QR code image
    $imageContent = @file_get_contents($url);

    if ($imageContent !== false) {
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="' . htmlspecialchars($filename) . '"');
        echo $imageContent;
        exit();
    } else {
        echo "Failed to fetch the QR code image.";
    }
} else {
    echo "No URL or filename provided.";
}
?>
