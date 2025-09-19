<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "zubieto-dental";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  // Use die() to stop the script and show an error message
  die("Connection failed: " . $conn->connect_error);
}
?>