<?php
require 'db.php';

// Query all users to get their current passwords
$sql = "SELECT users_id, password FROM users";
$result = $conn->query($sql);

if (!$result) {
    die("Error retrieving users: " . $conn->error);
}

// Rehash each password
while ($row = $result->fetch_assoc()) {
    $users_id = $row['users_id'];
    $plain_password = $row['password']; // Assuming stored as plain text

    // Generate a crypt-compatible hash
    $salt = substr(sha1(mt_rand()), 0, 22);
    $hashed_password = crypt($plain_password, '$2y$10$' . $salt);

    // Update the database with the hashed password
    $update_sql = "UPDATE users SET password = ? WHERE users_id = ?";
    $stmt = $conn->prepare($update_sql);
    if (!$stmt) {
        die("Error preparing update statement: " . $conn->error);
    }
    $stmt->bind_param("si", $hashed_password, $users_id);

    if ($stmt->execute()) {
        echo "Password rehashed for user ID: $users_id\n";
    } else {
        echo "Error updating password for user ID: $users_id - " . $stmt->error . "\n";
    }

    $stmt->close();
}

$conn->close();
echo "Password rehashing completed successfully.";
?>
