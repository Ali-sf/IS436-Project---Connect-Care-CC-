<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $password = isset($_POST['password']) ? trim($_POST['password']) : null;

    if ($email && $password) {
        $sql = "SELECT users_id, user_type, password FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($user_id, $user_type, $stored_password);

            if ($stmt->fetch()) {
                // Debugging Output
                echo "Debug: Stored Password: " . htmlspecialchars($stored_password) . "<br>";
                echo "Debug: Entered Password: " . htmlspecialchars($password) . "<br>";

                // Compare passwords
                if ($password === $stored_password) { // Change this if hashing passwords
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_type'] = $user_type;

                    // Redirect based on user type
                    if ($user_type === 'doctor') {
                        header("Location: doctor_dashboard.php");
                    } else {
                        header("Location: patient_dashboard.php");
                    }
                    exit();
                } else {
                    echo "<div class='alert alert-danger'>Invalid password.</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>User not found.</div>";
            }
            $stmt->close();
        } else {
            echo "<div class='alert alert-danger'>Error preparing query: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Please enter both email and password.</div>";
    }
}
$conn->close();
?>