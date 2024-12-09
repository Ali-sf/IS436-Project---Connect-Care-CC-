if (password_verify($password, $hashed_password)) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_type'] = $user_type;

    // Redirect based on user type
    if ($user_type === 'doctor') {
        header("Location: doctor_dashboard.html");
        exit();
    } else {
        header("Location: patient_dashboard.html");
        exit();
    }
} else {
    echo "Invalid password.";
}
