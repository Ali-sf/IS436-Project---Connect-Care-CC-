<?php
session_start();
require 'db.php'; // Include the database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Error: User not logged in. Please log in again.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the patient's appointments
$sql = "SELECT a.appointment_id, a.appointment_date, a.appointment_info, a.appointment_status, d.doctor_id, d.doctor_name
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.doctor_id
        JOIN patients p ON a.patient_id = p.patient_id
        WHERE p.users_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error preparing query: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($appointment_id, $appointment_date, $appointment_info, $appointment_status, $doctor_id, $doctor_name);

$appointments = [];
while ($stmt->fetch()) {
    $appointments[] = [
        'appointment_id' => $appointment_id,
        'appointment_date' => $appointment_date,
        'appointment_info' => $appointment_info,
        'appointment_status' => $appointment_status,
        'doctor_id' => $doctor_id,
        'doctor_name' => $doctor_name,
    ];
}

$stmt->close();

// Fetch available doctors for scheduling
$sql_doctors = "SELECT doctor_id, doctor_name FROM doctors";
$doctors_result = $conn->query($sql_doctors);

// Handle form submission for new appointments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_appointment'])) {
    $doctor_id = $_POST['doctor_id'];
    $appointment_info = $_POST['appointment_info'];
    $appointment_date = $_POST['appointment_date'];

    $sql_patient = "SELECT patient_id FROM patients WHERE users_id = ?";
    $stmt_patient = $conn->prepare($sql_patient);
    if (!$stmt_patient) {
        die("Error preparing query: " . $conn->error);
    }

    $stmt_patient->bind_param("i", $user_id);
    $stmt_patient->execute();
    $stmt_patient->bind_result($patient_id);

    if (!$stmt_patient->fetch()) {
        echo "<div class='alert alert-danger'>Error: Patient record not found for the logged-in user. Please contact support.</div>";
        $stmt_patient->close();
        $conn->close();
        exit();
    }
    $stmt_patient->close();

    $sql_insert = "INSERT INTO appointments (patient_id, doctor_id, appointment_info, appointment_date, appointment_status)
                   VALUES (?, ?, ?, ?, 'Scheduled')";
    $stmt_insert = $conn->prepare($sql_insert);

    if ($stmt_insert) {
        $stmt_insert->bind_param("iiss", $patient_id, $doctor_id, $appointment_info, $appointment_date);
        if ($stmt_insert->execute()) {
            echo "<div class='alert alert-success'>Appointment successfully scheduled!</div>";
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect to prevent duplicate submission
            exit();
        } else {
            echo "<div class='alert alert-danger'>Error scheduling appointment: " . $stmt_insert->error . "</div>";
        }
        $stmt_insert->close();
    } else {
        echo "<div class='alert alert-danger'>Error preparing query: " . $conn->error . "</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>My Appointments</h1>
        <?php if (!empty($appointments)): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th>Doctor</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['appointment_info']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['appointment_status']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                            <td>
                                <?php if ($appointment['appointment_status'] === 'Scheduled'): ?>
                                    <a href="cancel_appointment.php?appointment_id=<?php echo $appointment['appointment_id']; ?>&doctor_id=<?php echo $appointment['doctor_id']; ?>" class="btn btn-danger btn-sm">Cancel</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No appointments found.</p>
        <?php endif; ?>

        <h2>Schedule a New Appointment</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="doctor_id" class="form-label">Select Doctor</label>
                <select class="form-select" id="doctor_id" name="doctor_id" required>
                    <option value="" disabled selected>Choose a doctor</option>
                    <?php while ($doctor = $doctors_result->fetch_assoc()): ?>
                        <option value="<?php echo $doctor['doctor_id']; ?>">
                            <?php echo htmlspecialchars($doctor['doctor_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="appointment_info" class="form-label">Appointment Details</label>
                <input type="text" class="form-control" id="appointment_info" name="appointment_info" required>
            </div>
            <div class="mb-3">
                <label for="appointment_date" class="form-label">Appointment Date</label>
                <input type="datetime-local" class="form-control" id="appointment_date" name="appointment_date" required>
            </div>
            <button type="submit" name="schedule_appointment" class="btn btn-primary">Schedule Appointment</button>
        </form>

        <div class="mt-4 text-center">
    <a href="patient_dashboard.php" class="btn btn-secondary">
        Back to Dashboard
    </a>
</div>

    </div>
</body>
</html>
