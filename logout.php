<?php
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login page with success message
header('Location: login.php?message=You have been logged out successfully');
exit();
?>