<?php
// receiver-login.php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid request";
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    echo "Please provide email and password";
    exit;
}

$stmt = $conn->prepare("SELECT receiver_id, org_name, receiver_name, email, password FROM receivers WHERE email = ?");
if (!$stmt) {
    echo "Database error: " . $conn->error;
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $receiver = $result->fetch_assoc();

    // plain-text password comparison (since no hashing used)
    if ($password === $receiver['password']) {
        $_SESSION['receiver_id'] = $receiver['receiver_id'];
        $_SESSION['receiver_name'] = $receiver['receiver_name'];
        $_SESSION['receiver_email'] = $receiver['email'];
        $_SESSION['receiver_org'] = $receiver['org_name'];
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
