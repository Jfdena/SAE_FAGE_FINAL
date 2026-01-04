<?php
session_start();

if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit();
