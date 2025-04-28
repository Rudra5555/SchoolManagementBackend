<?php
// Check if 'file' parameter is passed via GET
if (isset($_GET['file'])) {
    // Sanitize and get the file name
    $file = basename($_GET['file']);
    $filepath = 'receipts/' . $file; // receipts folder me hi file allowed hai

    // Check if the file exists
    if (file_exists($filepath)) {
        // Force download headers
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath); // Output file to browser
        exit;
    } else {
        echo "File not found.";
    }
} else {
    echo "No file specified.";
}
?>
