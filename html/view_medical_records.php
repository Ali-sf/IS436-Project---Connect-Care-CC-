<?php
session_start();
require 'db.php'; // Include the database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Error: User not logged in. Please log in again.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the user's type
$sql_user_type = "SELECT user_type FROM users WHERE users_id = ?";
$stmt_user_type = $conn->prepare($sql_user_type);
if (!$stmt_user_type) {
    die("Error preparing query: " . $conn->error);
}
$stmt_user_type->bind_param("i", $user_id);
$stmt_user_type->execute();
$stmt_user_type->bind_result($user_type);
$stmt_user_type->fetch();
$stmt_user_type->close();

// Ensure the user type is valid
if (!$user_type) {
    die("Error: Unable to determine user type.");
}

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

if (!$patient_id) {
    die("Error: Patient ID not found.");
}

// Handle adding a new medical record
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $record_date = $_POST['record_date'];
    $description = $_POST['description'];
    $doctor_id = $_POST['doctor_id'];
    $uploaded_file = $_FILES['record_file'];

    // Handle file upload
    $file_path = null;
    if ($uploaded_file['tmp_name']) {
        $target_dir = "uploads/";
        $file_path = $target_dir . basename($uploaded_file['name']);
        move_uploaded_file($uploaded_file['tmp_name'], $file_path);
    }

    // Insert the new record
    $sql_insert = "INSERT INTO medicalrecords (patient_id, doctor_id, record_date, description, record_file)
                   VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    if (!$stmt_insert) {
        die("Error preparing query: " . $conn->error);
    }
    $stmt_insert->bind_param("iisss", $patient_id, $doctor_id, $record_date, $description, $file_path);
    if ($stmt_insert->execute()) {
        echo "<div class='alert alert-success'>Medical record added successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error adding record: " . $stmt_insert->error . "</div>";
    }
    $stmt_insert->close();
}

// Fetch the patient's medical records
$sql = "SELECT m.record_date, m.description, m.record_file, d.doctor_name
        FROM medicalrecords m
        JOIN doctors d ON m.doctor_id = d.doctor_id
        WHERE m.patient_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing query: " . $conn->error);
}
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$stmt->bind_result($record_date, $description, $record_file, $doctor_name);

$medical_records = [];
while ($stmt->fetch()) {
    $medical_records[] = [
        'record_date' => $record_date,
        'description' => $description,
        'record_file' => $record_file,
        'doctor_name' => $doctor_name,
    ];
}
$stmt->close();

// Fetch the list of doctors from the database
$sql_doctors = "SELECT doctor_id, doctor_name FROM doctors";
$result_doctors = $conn->query($sql_doctors);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Medical Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>My Medical Records</h1>
        <?php if (!empty($medical_records)): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Doctor</th>
                        <th>File</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medical_records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['record_date']); ?></td>
                            <td><?php echo htmlspecialchars($record['description']); ?></td>
                            <td><?php echo htmlspecialchars($record['doctor_name']); ?></td>
                            <td>
                                <?php if ($record['record_file']): ?>
                                    <a href="uploads/<?php echo htmlspecialchars($record['record_file']); ?>" download>Download</a>
                                <?php else: ?>
                                    No file
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No medical records found.</p>
        <?php endif; ?>

        <div class="mt-4">
            <h3>Add a New Medical Record</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="record_date" class="form-label">Record Date</label>
                    <input type="date" name="record_date" id="record_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="doctor_id" class="form-label">Doctor</label>
                    <select name="doctor_id" id="doctor_id" class="form-select" required>
                        <?php while ($doctor = $result_doctors->fetch_assoc()): ?>
                            <option value="<?php echo $doctor['doctor_id']; ?>">
                                <?php echo htmlspecialchars($doctor['doctor_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="record_file" class="form-label">File</label>
                    <input type="file" name="record_file" id="record_file" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Add Record</button>
            </form>
        </div>

        <div class="mt-4 text-center">
            <a href="<?php echo ($user_type === 'patient') ? 'patient_dashboard.php' : 'doctor_dashboard.php'; ?>" 
               class="btn btn-secondary">
               Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
