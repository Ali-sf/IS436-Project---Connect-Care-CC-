<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="text-center mb-5">
            <h1 class="display-5">
                <?php
                session_start();
                require 'db.php';

                $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

                if (!$user_id) {
                    echo "Error: User ID not set. Please log in again.";
                    exit();
                }

                $sql = "SELECT doctor_name FROM doctors WHERE users_id = ?";
                $stmt = $conn->prepare($sql);

                if (!$stmt) {
                    echo "Error preparing query: " . $conn->error;
                    exit();
                }

                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->bind_result($doctor_name);

                if ($stmt->fetch()) {
                    echo "Welcome, " . htmlspecialchars($doctor_name) . "!";
                } else {
                    echo "Error: Doctor not found for user ID $user_id.";
                }

                $stmt->close();
                $conn->close();
                ?>
            </h1>
            <a href="logout.php" class="btn btn-warning mt-3">Logout</a>
        </div>

        <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center">
            <div class="col d-flex justify-content-center">
                <div class="card mb-3 w-100 h-100 text-center">
                    <div class="card-body">
                        <h4 class="card-title">Manage Appointments</h4>
                        <p class="card-text">View, update, or delete appointments.</p>
                        <a href="manage_appointments.php" class="btn btn-primary">Go to Appointments</a>
                    </div>
                </div>
            </div>

            <div class="col d-flex justify-content-center">
                <div class="card mb-3 w-100 h-100 text-center">
                    <div class="card-body">
                        <h4 class="card-title">View Patient Records</h4>
                        <p class="card-text">Access detailed patient records and histories.</p>
                        <a href="view_patients.php" class="btn btn-primary">View Records</a>
                    </div>
                </div>
            </div>

            <div class="col d-flex justify-content-center">
                <div class="card mb-3 w-100 h-100 text-center">
                    <div class="card-body">
                        <h4 class="card-title">Messages</h4>
                        <p class="card-text">Communicate with your patients.</p>
                        <a href="messages.php" class="btn btn-primary">Go to Messages</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
