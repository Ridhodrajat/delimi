<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "desainweb"; 

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) die("Gagal Connect: " . mysqli_connect_error());
?>