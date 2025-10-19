<?php
// Start the session
session_start();

// Destroy all session variables
session_unset();  // Remove all session variables
session_destroy(); // Destroy the session

// Redirect to the login page
header('Location: index.php?action=home');
exit;
?>
