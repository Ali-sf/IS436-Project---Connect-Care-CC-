<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="text-center mb-4">
            <h1 class="display-5">
                <?php
                session_start();
                require 'db.php';

                $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

                if (!$user_id) {
                    echo "Error: User ID not set. Please log in again.";
                    exit();
                }

                $sql = "SELECT first_name, last_name FROM patients WHERE users_id = ?";
                $stmt = $conn->prepare($sql);

                if (!$stmt) {
                    echo "Error preparing query: " . $conn->error;
                    exit();
                }

                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->bind_result($first_name, $last_name);

                if ($stmt->fetch()) {
                    echo "Welcome, " . htmlspecialchars($first_name) . " " . htmlspecialchars($last_name) . "!";
                } else {
                    echo "Error: Patient not found for user ID $user_id.";
                }

                $stmt->close();
                $conn->close();
                ?>
            </h1>
            <a href="logout.php" class="btn btn-warning mt-3">Logout</a>
        </div>

        <div class="row row-cols-1 row-cols-md-4 g-3">
            <div class="col d-flex">
                <div class="card mb-3 w-100 h-100">
                    <div class="card-body">
                        <h4 class="card-title">View Appointments</h4>
                        <p class="card-text">Manage your scheduled appointments.</p>
                        <a href="view_appointments.php" class="btn btn-primary">View Appointments</a>
                    </div>
                </div>
            </div>

            <div class="col d-flex">
                <div class="card mb-3 w-100 h-100">
                    <div class="card-body">
                        <h4 class="card-title">View Medical Records</h4>
                        <p class="card-text">Access your detailed medical records.</p>
                        <a href="view_medical_records.php" class="btn btn-primary">View Records</a>
                    </div>
                </div>
            </div>

            <div class="col d-flex">
                <div class="card mb-3 w-100 h-100">
                    <div class="card-body">
                        <h4 class="card-title">Messages</h4>
                        <p class="card-text">Communicate with your doctor.</p>
                        <a href="messages.php" class="btn btn-primary">Go to Messages</a>
                    </div>
                </div>
            </div>

            <div class="col d-flex">
                <div class="card mb-3 w-100 h-100">
                    <div class="card-body">
                        <h4 class="card-title">View Medical Bills</h4>
                        <p class="card-text">Review and manage your medical bills.</p>
                        <a href="view_medical_bills.php" class="btn btn-primary">View Bills</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
