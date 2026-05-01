<?php
session_start();
session_unset();    // Remove all session variables
session_destroy();  // Destroy the session itself

// Send them back to the front door
header("Location: ./login.php");
exit();
?>