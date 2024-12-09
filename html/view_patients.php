<?php
session_start();
require 'db.php'; // Include the database connection

// Check if the doctor is logged in
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

// Fetch patients and their medical records associated with the doctor
$sql_records = "
    SELECT 
        p.patient_id,
        p.first_name,
        p.last_name,
        mr.record_id,
        mr.record_date,
        mr.description,
        mr.record_file
    FROM 
        patients p
    JOIN 
        medicalrecords mr ON p.patient_id = mr.patient_id
    WHERE 
        mr.doctor_id = ?
    ORDER BY 
        mr.record_date DESC";
$stmt_records = $conn->prepare($sql_records);
if (!$stmt_records) {
    die("Error preparing query: " . $conn->error);
}
$stmt_records->bind_param("i", $doctor_id);
$stmt_records->execute();
$stmt_records->bind_result($patient_id, $first_name, $last_name, $record_id, $record_date, $description, $record_file);

$records = [];
while ($stmt_records->fetch()) {
    $records[] = [
        'patient_id' => $patient_id,
        'patient_name' => $first_name . ' ' . $last_name,
        'record_id' => $record_id,
        'record_date' => $record_date,
        'description' => $description,
        'record_file' => $record_file,
    ];
}
$stmt_records->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Patients' Medical Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Patients' Medical Records</h1>
        <?php if (!empty($records)): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Record Date</th>
                        <th>Description</th>
                        <th>File</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($record['record_date']); ?></td>
                            <td><?php echo htmlspecialchars($record['description']); ?></td>
                            <td>
                                <?php if (!empty($record['record_file'])): ?>
                                    <a href="uploads/<?php echo htmlspecialchars($record['record_file']); ?>" class="btn btn-link" target="_blank">View File</a>
                                <?php else: ?>
                                    No File
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No medical records found for your patients.</p>
        <?php endif; ?>
        <div class="text-center mt-4">
            <a href="doctor_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
