<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: LOGIN.html');
    exit;
}

// Role checking functions
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function isTeacher() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'teacher';
}
?>