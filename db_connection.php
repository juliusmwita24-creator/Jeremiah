<?php
$servername = "localhost";
$username = "root";         // Default user from XAMPP
$password = "mu12345678";             // Default password is empty
$dbname = "wakalasmart";

// Tengeneza connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Kagua kama connection imefanikiwa
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: weka charset (UTF8)
$conn->set_charset("utf8");

// Connection imefanikiwa
// echo "Connected successfully";
?>
