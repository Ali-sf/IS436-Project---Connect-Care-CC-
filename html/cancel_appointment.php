<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Error: User not logged in. Please log in again.";
    exit();
}

$user_id = $_SESSION['user_id'];
$appointment_id = isset($_GET['appointment_id']) ? $_GET['appointment_id'] : null;
$doctor_id = isset($_GET['doctor_id']) ? $_GET['doctor_id'] : null;

if (!$appointment_id || !$doctor_id) {
    echo "Error: Missing appointment or doctor information.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cancellation_reason = trim($_POST['cancellation_reason']);

    if (empty($cancellation_reason)) {
        echo "<div class='alert alert-danger'>Cancellation reason is required.</div>";
    } else {
        // Fetch the patient's ID
        $sql_patient = "SELECT patient_id FROM patients WHERE users_id = ?";
        $stmt_patient = $conn->prepare($sql_patient);
        if (!$stmt_patient) {
            die("Error preparing query: " . $conn->error);
        }
        $stmt_patient->bind_param("i", $user_id);
        $stmt_patient->execute();
        $stmt_patient->bind_result($patient_id);
        $stmt_patient->fetch();
        $stmt_patient->close();

        // Insert the cancellation reason into the appointment_cancellations table
        $sql_cancellation = "INSERT INTO appointment_cancellations (appointment_id, patient_id, doctor_id, cancellation_reason)
                             VALUES (?, ?, ?, ?)";
        $stmt_cancellation = $conn->prepare($sql_cancellation);

        if ($stmt_cancellation) {
            $stmt_cancellation->bind_param("iiis", $appointment_id, $patient_id, $doctor_id, $cancellation_reason);
            if ($stmt_cancellation->execute()) {
                // Update the appointment status in the appointments table
                $sql_update_status = "UPDATE appointments SET appointment_status = 'Cancelled' WHERE appointment_id = ?";
                $stmt_update = $conn->prepare($sql_update_status);
                if ($stmt_update) {
                    $stmt_update->bind_param("i", $appointment_id);
                    if ($stmt_update->execute()) {
                        echo "<div class='alert alert-success'>Appointment successfully updated to 'Cancelled'!</div>";
                    } else {
                        echo "<div class='alert alert-danger'>Error updating appointment status: " . $stmt_update->error . "</div>";
                    }
                    $stmt_update->close();
                } else {
                    echo "<div class='alert alert-danger'>Error preparing update query: " . $conn->error . "</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Error saving cancellation: " . $stmt_cancellation->error . "</div>";
            }
            $stmt_cancellation->close();
        } else {
            echo "<div class='alert alert-danger'>Error preparing cancellation query: " . $conn->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Cancel Appointment</h1>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="cancellation_reason" class="form-label">Reason for Cancellation</label>
                <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-danger">Submit Cancellation</button>
            <a href="view_appointments.php" class="btn btn-secondary">Back</a>
        </form>
    </div>
</body>
</html>
