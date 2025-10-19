<?php
// api/download_pdf.php

// Check if filename is provided
if (!isset($_GET['file']) || empty($_GET['file'])) {
    header('HTTP/1.1 400 Bad Request');
    echo 'No file specified for download';
    exit;
}

// Sanitize filename to prevent directory traversal attacks
$filename = basename($_GET['file']);
$filepath = __DIR__ . '/../temp/' . $filename;

// Check if file exists and is readable
if (!file_exists($filepath) || !is_readable($filepath)) {
    header('HTTP/1.1 404 Not Found');
    echo 'File not found or not readable';
    exit;
}

// Set headers for download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output file content
readfile($filepath);

// Delete file after download to clean up
unlink($filepath);
exit;