<?php
session_start();
include 'db.php';

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    echo "Please enter both email and password.";
    exit;
}

$sql = "SELECT * FROM volunteer WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $volunteer = $result->fetch_assoc();

    if ($password === $volunteer['password']) { // (use password_hash later)
        $_SESSION['volunteer_id'] = $volunteer['volunteer_id'];
        $_SESSION['volunteer_email'] = $volunteer['email'];
        echo "success";
    } else {
        echo "Invalid password";
    }
} else {
    echo "Email not found";
}

$stmt->close();
$conn->close();
?>
