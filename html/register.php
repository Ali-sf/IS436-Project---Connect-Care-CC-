<?php
session_start();
require 'db.php';

header('Content-Type: text/plain');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Collect and validate POST data
$user_type = isset($_POST['user_type']) ? $_POST['user_type'] : null;
$email = isset($_POST['email']) ? trim($_POST['email']) : null;
$password = isset($_POST['password']) ? trim($_POST['password']) : null;

// Validate required fields
if (!$user_type || !$email || !$password) {
    die("Error: All fields are required.");
}

try {
    // Start a database transaction
    $conn->query("START TRANSACTION");

    // Insert into the `users` table
    $sql_users = "INSERT INTO users (email, password, user_type) VALUES (?, ?, ?)";
    $stmt_users = $conn->prepare($sql_users);
    if (!$stmt_users) {
        throw new Exception("Error preparing users query: " . $conn->error);
    }
    $stmt_users->bind_param("sss", $email, $password, $user_type);
    if (!$stmt_users->execute()) {
        throw new Exception("Error executing users query: " . $stmt_users->error);
    }

    // Get the inserted user ID
    $user_id = $conn->insert_id;

    // Additional processing based on user type
    if ($user_type === 'doctor') {
        // Collect and validate doctor-specific fields
        $doctor_name = isset($_POST['doctor_name']) ? trim($_POST['doctor_name']) : null;
        $workplace = isset($_POST['workplace']) ? trim($_POST['workplace']) : null;

        if (!$doctor_name || !$workplace) {
            throw new Exception("Error: Doctor's name and workplace are required.");
        }

        // Insert into the `doctors` table
        $sql_doctors = "INSERT INTO doctors (users_id, doctor_name, workplace) VALUES (?, ?, ?)";
        $stmt_doctors = $conn->prepare($sql_doctors);
        if (!$stmt_doctors) {
            throw new Exception("Error preparing doctors query: " . $conn->error);
        }
        $stmt_doctors->bind_param("iss", $user_id, $doctor_name, $workplace);
        if (!$stmt_doctors->execute()) {
            throw new Exception("Error executing doctors query: " . $stmt_doctors->error);
        }
        $stmt_doctors->close();
    } elseif ($user_type === 'patient') {
        // Collect and validate patient-specific fields
        $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : null;
        $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : null;
        $date_of_birth = isset($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;

        if (!$first_name || !$last_name || !$date_of_birth) {
            throw new Exception("Error: Patient's first name, last name, and date of birth are required.");
        }

        // Insert into the `patients` table
        $sql_patients = "INSERT INTO patients (users_id, first_name, last_name, date_of_birth) VALUES (?, ?, ?, ?)";
        $stmt_patients = $conn->prepare($sql_patients);
        if (!$stmt_patients) {
            throw new Exception("Error preparing patients query: " . $conn->error);
        }
        $stmt_patients->bind_param("isss", $user_id, $first_name, $last_name, $date_of_birth);
        if (!$stmt_patients->execute()) {
            throw new Exception("Error executing patients query: " . $stmt_patients->error);
        }
        $stmt_patients->close();
    }

    // Commit the transaction
    $conn->query("COMMIT");

    // Set session variables and redirect the user
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_type'] = $user_type;

    if ($user_type === 'doctor') {
        header("Location: doctor_dashboard.php");
    } else {
        header("Location: patient_dashboard.php");
    }
    exit();
} catch (Exception $e) {
    // Rollback the transaction in case of any error
    $conn->query("ROLLBACK");
    echo "Error: " . $e->getMessage();
}

// Cleanup
$stmt_users->close();
$conn->close();
?>
