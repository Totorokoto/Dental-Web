<?php
// Set the default timezone for all date/time functions in the application
date_default_timezone_set('Asia/Manila');

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dental_monitoring";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  // Use die() to stop the script and show an error message
  die("Connection failed: " . $conn->connect_error);
}
?>