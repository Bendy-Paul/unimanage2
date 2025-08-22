<?php
require_once '../includes/auth.php';
// destroy session and redirect
session_start();
$_SESSION = [];
session_destroy();
header('Location: ../index.php');
exit();
