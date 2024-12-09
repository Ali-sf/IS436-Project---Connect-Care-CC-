<?php
// Correct database connection details
$servername = "studentdb-maria.gl.umbc.edu";
$username = "nm03056"; // Replace with your username
$password = "nm03056"; // Replace with your password
$database = "nm03056"; // Replace with your database name

// Create a new connection
$conn = new mysqli($servername, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
