<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();
session_destroy();
setcookie(session_name(), '', time() - 3600); // Supprime le cookie de session

header("Location: index.php");
exit;
?>
