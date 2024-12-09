<?php
session_start();
require 'db.php'; // Include the database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Error: User not logged in. Please log in again.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Determine the user's type (doctor or patient)
$sql_user_type = "SELECT user_type FROM users WHERE users_id = ?";
$stmt_user_type = $conn->prepare($sql_user_type);
if (!$stmt_user_type) {
    die("Error preparing user type query: " . $conn->error);
}
$stmt_user_type->bind_param("i", $user_id);
$stmt_user_type->execute();
$stmt_user_type->bind_result($user_type);
$stmt_user_type->fetch();
$stmt_user_type->close();

// Fetch recipients based on the user's type
if ($user_type === 'patient') {
    // Fetch all doctors for the dropdown menu
    $sql_recipients = "
        SELECT d.doctor_id, d.doctor_name, d.users_id AS recipient_user_id 
        FROM doctors d 
        JOIN users u ON d.users_id = u.users_id";
} elseif ($user_type === 'doctor') {
    // Fetch all patients for the dropdown menu
    $sql_recipients = "
        SELECT p.patient_id, CONCAT(p.first_name, ' ', p.last_name) AS patient_name, p.users_id AS recipient_user_id 
        FROM patients p 
        JOIN users u ON p.users_id = u.users_id";
} else {
    echo "<div class='alert alert-danger text-center'>Error: Unauthorized access.</div>";
    exit();
}

$result_recipients = $conn->query($sql_recipients);
if (!$result_recipients) {
    die("Error fetching recipients: " . $conn->error);
}

// Convert recipients into an associative array for validation
$recipients = [];
while ($row = $result_recipients->fetch_assoc()) {
    $recipients[$row['recipient_user_id']] = [
        'name' => $user_type === 'patient' ? $row['doctor_name'] : $row['patient_name'],
    ];
}

// Handle recipient selection
$receiver_id = null;
$receiver_name = null;

// Persist recipient selection
$receiver_user_id = isset($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : (isset($_POST['persisted_doctor_id']) ? (int)$_POST['persisted_doctor_id'] : null);

if ($receiver_user_id) {
    // Validate recipient exists
    if (!isset($recipients[$receiver_user_id])) {
        echo "<div class='alert alert-danger'>Error: Recipient not found. Please select a valid recipient.</div>";
        exit();
    }

    $receiver_name = $recipients[$receiver_user_id]['name'];
    $receiver_id = $receiver_user_id;
}

// Handle sending a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_text'], $receiver_id)) {
    $message_text = trim($_POST['message_text']);

    if (!empty($message_text)) {
        $sql_send = "
            INSERT INTO messages (sender_id, receiver_id, message_text, sent_date) 
            VALUES (?, ?, ?, NOW())";
        $stmt_send = $conn->prepare($sql_send);
        if (!$stmt_send) {
            die("Error preparing message insert query: " . $conn->error);
        }
        $stmt_send->bind_param("iis", $user_id, $receiver_id, $message_text);

        if ($stmt_send->execute()) {
            echo "<div class='alert alert-success text-center'>Message sent successfully!</div>";
        } else {
            echo "<div class='alert alert-danger text-center'>Error sending message: " . $stmt_send->error . "</div>";
        }
        $stmt_send->close();
    } else {
        echo "<div class='alert alert-danger text-center'>Message cannot be empty.</div>";
    }
}

// Fetch previous messages between the user and the selected recipient
$messages = [];
if ($receiver_id) {
    $sql_messages = "
        SELECT 
            m.sender_id, 
            m.message_text, 
            m.sent_date, 
            CASE
                WHEN u.user_type = 'doctor' THEN (SELECT doctor_name FROM doctors WHERE doctors.users_id = m.sender_id)
                WHEN u.user_type = 'patient' THEN (SELECT CONCAT(first_name, ' ', last_name) FROM patients WHERE patients.users_id = m.sender_id)
            END AS sender_name
        FROM messages m
        JOIN users u ON u.users_id = m.sender_id
        WHERE 
            (m.sender_id = ? AND m.receiver_id = ?) 
            OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.sent_date ASC";

    $stmt_messages = $conn->prepare($sql_messages);
    if (!$stmt_messages) {
        die("Error preparing messages query: " . $conn->error);
    }
    $stmt_messages->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
    $stmt_messages->execute();
    $stmt_messages->bind_result($sender_id, $message_text, $sent_date, $sender_name);

    while ($stmt_messages->fetch()) {
        $messages[] = [
            'sender_id' => $sender_id,
            'message_text' => $message_text,
            'sent_date' => $sent_date,
            'sender_name' => $sender_name,
        ];
    }

    $stmt_messages->close();
}

$conn->close();
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Messages</h1>

        <form method="POST" action="" class="mb-4">
    <div class="mb-3">
        <label for="doctor_id" class="form-label">Select Recipient</label>
        <select name="doctor_id" id="doctor_id" class="form-select" onchange="this.form.submit()" required>
            <option value="" disabled selected>Select a Recipient</option>
            <?php foreach ($recipients as $id => $recipient): ?>
                <option value="<?php echo $id; ?>" <?php echo ($id == $receiver_id) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($recipient['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <input type="hidden" name="persisted_doctor_id" value="<?php echo htmlspecialchars($receiver_id); ?>">
</form>



        <?php if ($receiver_id): ?>
            <h2 class="text-center">Messages with <?php echo htmlspecialchars($receiver_name); ?></h2>

            <div class="card mb-4">
            <div class="card-body" style="max-height: 400px; overflow-y: scroll;">
    <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $message): ?>
            <div class="mb-3">
                <strong><?php echo htmlspecialchars($message['sender_name']); ?>:</strong>
                <p><?php echo htmlspecialchars($message['message_text']); ?></p>
                <small class="text-muted"><?php echo htmlspecialchars($message['sent_date']); ?></small>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-center">No messages yet.</p>
    <?php endif; ?>
</div>

            </div>

            <form method="POST" action="">
                <div class="input-group">
                    <input type="hidden" name="doctor_id" value="<?php echo $receiver_id; ?>">
                    <input type="text" name="message_text" class="form-control" placeholder="Type your message..." required>
                    <button type="submit" class="btn btn-primary">Send</button>
                </div>
            </form>
        <?php else: ?>
            <p class="text-center">Select a doctor to start messaging.</p>
        <?php endif; ?>

        <div class="mt-4 text-center">
    <a href="<?php echo ($user_type === 'patient') ? 'patient_dashboard.php' : 'doctor_dashboard.php'; ?>" 
       class="btn btn-secondary">
       Back to Dashboard
    </a>
</div>
    </div>
</body>
</html>
