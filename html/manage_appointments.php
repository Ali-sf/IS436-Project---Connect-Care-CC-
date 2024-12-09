<?php
session_start();
require 'db.php'; // Include the database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Error: User not logged in. Please log in again.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the doctor's ID based on the logged-in user
$sql_doctor = "SELECT doctor_id FROM doctors WHERE users_id = ?";
$stmt_doctor = $conn->prepare($sql_doctor);

if (!$stmt_doctor) {
    die("Error preparing query: " . $conn->error);
}

$stmt_doctor->bind_param("i", $user_id);
$stmt_doctor->execute();
$stmt_doctor->bind_result($doctor_id);

if (!$stmt_doctor->fetch()) {
    echo "Error: Doctor not found for user ID $user_id.";
    exit();
}
$stmt_doctor->close();

// Fetch all appointments for this doctor
$sql_appointments = "SELECT a.appointment_id, p.patient_id, CONCAT(p.first_name, ' ', p.last_name) AS patient_name, 
                            a.appointment_date, a.appointment_info, a.appointment_status 
                     FROM appointments a
                     JOIN patients p ON a.patient_id = p.patient_id
                     WHERE a.doctor_id = ?";
$stmt_appointments = $conn->prepare($sql_appointments);

if (!$stmt_appointments) {
    die("Error preparing query: " . $conn->error);
}

$stmt_appointments->bind_param("i", $doctor_id);
$stmt_appointments->execute();
$stmt_appointments->bind_result($appointment_id, $patient_id, $patient_name, $appointment_date, $appointment_info, $appointment_status);

$appointments = [];
while ($stmt_appointments->fetch()) {
    $appointments[] = [
        'appointment_id' => $appointment_id,
        'patient_id' => $patient_id,
        'patient_name' => $patient_name,
        'appointment_date' => $appointment_date,
        'appointment_info' => $appointment_info,
        'appointment_status' => $appointment_status,
    ];
}
$stmt_appointments->close();

// Handle form submissions to update or delete appointments
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        // Update appointment status
        $appointment_id = $_POST['appointment_id'];
        $new_status = $_POST['appointment_status'];

        $sql_update = "UPDATE appointments SET appointment_status = ? WHERE appointment_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        if ($stmt_update) {
            $stmt_update->bind_param("si", $new_status, $appointment_id);
            if ($stmt_update->execute()) {
                echo "<div class='alert alert-success'>Appointment updated successfully!</div>";

                // Generate bill for "Completed" status
                if ($new_status === "Completed") {
                    $bill_amount = null; // Set amount to NULL (To Be Decided)
                    $bill_due_date = date('Y-m-d', strtotime('+30 days'));
                
                    // Check if a bill already exists for the patient and appointment
                    $sql_check = "SELECT COUNT(*) FROM medicalbills WHERE patient_id = ? AND bill_status = 'Unpaid'";
                    $stmt_check = $conn->prepare($sql_check);
                    if ($stmt_check) {
                        $stmt_check->bind_param("i", $_POST['patient_id']);
                        $stmt_check->execute();
                        $stmt_check->bind_result($bill_count);
                        $stmt_check->fetch();
                        $stmt_check->close();
                
                        // Only insert a bill if no unpaid bill exists for the patient
                        if ($bill_count === 0) {
                            $sql_bill = "INSERT INTO medicalbills (patient_id, bill_amount, bill_status, bill_due_date)
                                         VALUES (?, ?, 'Unpaid', ?)";
                            $stmt_bill = $conn->prepare($sql_bill);
                            if ($stmt_bill) {
                                $stmt_bill->bind_param("iis", $_POST['patient_id'], $bill_amount, $bill_due_date);
                                $stmt_bill->execute();
                                $stmt_bill->close();
                            }
                        }
                    }
                }
                
            } else {
                echo "<div class='alert alert-danger'>Error updating appointment: " . $stmt_update->error . "</div>";
            }
            $stmt_update->close();
        } else {
            echo "<div class='alert alert-danger'>Error preparing update query: " . $conn->error . "</div>";
        }
    } elseif (isset($_POST['delete'])) {
        // Delete appointment
        $appointment_id = $_POST['appointment_id'];

        $sql_delete = "DELETE FROM appointments WHERE appointment_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        if ($stmt_delete) {
            $stmt_delete->bind_param("i", $appointment_id);
            if ($stmt_delete->execute()) {
                echo "<div class='alert alert-success'>Appointment deleted successfully!</div>";
            } else {
                echo "<div class='alert alert-danger'>Error deleting appointment: " . $stmt_delete->error . "</div>";
            }
            $stmt_delete->close();
        } else {
            echo "<div class='alert alert-danger'>Error preparing delete query: " . $conn->error . "</div>";
        }
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Manage Appointments</h1>
        <?php if (!empty($appointments)): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Date</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['appointment_info']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['appointment_status']); ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                    <input type="hidden" name="patient_id" value="<?php echo $appointment['patient_id']; ?>">
                                    <select name="appointment_status" class="form-select mb-2">
                                        <option value="Scheduled" <?php echo $appointment['appointment_status'] === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                        <option value="Completed" <?php echo $appointment['appointment_status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="Cancelled" <?php echo $appointment['appointment_status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update" class="btn btn-primary btn-sm">Update</button>
                                    <button type="submit" name="delete" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No appointments found.</p>
        <?php endif; ?>

        <a href="doctor_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
</body>
</html>
