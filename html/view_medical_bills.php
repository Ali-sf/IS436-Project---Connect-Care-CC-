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

// Restrict doctors from accessing this page
if ($user_type === 'doctor') {
    echo "<div class='alert alert-danger text-center'>Access Denied: Only patients can view medical bills.</div>";
    exit();
}

// Fetch medical bills for the logged-in patient
$sql_bills = "
    SELECT 
        mb.bill_id,
        mb.bill_amount,
        mb.bill_status,
        mb.bill_due_date,
        CONCAT(p.first_name, ' ', p.last_name) AS patient_name
    FROM medicalbills mb
    JOIN patients p ON mb.patient_id = p.patient_id
    WHERE p.users_id = ?";
$stmt_bills = $conn->prepare($sql_bills);
if (!$stmt_bills) {
    die("Error preparing query: " . $conn->error);
}
$stmt_bills->bind_param("i", $user_id);
$stmt_bills->execute();
$stmt_bills->bind_result($bill_id, $bill_amount, $bill_status, $bill_due_date, $patient_name);

$bills = [];
while ($stmt_bills->fetch()) {
    $bills[] = [
        'bill_id' => $bill_id,
        'bill_amount' => $bill_amount,
        'bill_status' => $bill_status,
        'bill_due_date' => $bill_due_date,
        'patient_name' => $patient_name,
    ];
}
$stmt_bills->close();

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_bill'])) {
    $bill_id = $_POST['bill_id'];

    $sql_pay = "UPDATE medicalbills SET bill_status = 'Paid' WHERE bill_id = ?";
    $stmt_pay = $conn->prepare($sql_pay);
    if ($stmt_pay) {
        $stmt_pay->bind_param("i", $bill_id);
        if ($stmt_pay->execute()) {
            echo "<div class='alert alert-success text-center'>Payment successful for bill ID $bill_id!</div>";
        } else {
            echo "<div class='alert alert-danger text-center'>Error processing payment: " . $stmt_pay->error . "</div>";
        }
        $stmt_pay->close();
    } else {
        echo "<div class='alert alert-danger text-center'>Error preparing query: " . $conn->error . "</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Medical Bills</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">My Medical Bills</h1>

        <?php if (!empty($bills)): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    <?php foreach ($bills as $bill): ?>
        <tr>
            <td>
                <?php echo $bill['bill_amount'] !== null 
                    ? "$" . htmlspecialchars(number_format($bill['bill_amount'], 2)) 
                    : "To Be Decided"; ?>
            </td>
            <td><?php echo htmlspecialchars($bill['bill_status']); ?></td>
            <td><?php echo htmlspecialchars($bill['bill_due_date']); ?></td>
            <td>
                <?php if ($bill['bill_status'] === 'Unpaid' && $bill['bill_amount'] !== null): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="bill_id" value="<?php echo $bill['bill_id']; ?>">
                        <button type="submit" name="pay_bill" class="btn btn-success btn-sm">Pay Now</button>
                    </form>
                <?php elseif ($bill['bill_amount'] === null): ?>
                    <button class="btn btn-warning btn-sm" disabled>Pending Amount</button>
                <?php else: ?>
                    <button class="btn btn-secondary btn-sm" disabled>Paid</button>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>

            </table>
        <?php else: ?>
            <p class="text-center">No medical bills found.</p>
        <?php endif; ?>

        <div class="mt-4 text-center">
            <a href="patient_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
