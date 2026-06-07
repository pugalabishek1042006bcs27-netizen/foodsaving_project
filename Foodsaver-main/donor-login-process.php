<?php
session_start();
include 'db.php'; // Ensure this connects to the foodsaver DB

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$email || !$password) {
        echo "Please enter both email and password!";
        exit;
    }

    // ✅ Use the correct table name: donor (not donors)
    $stmt = $conn->prepare("SELECT donor_id, password FROM donor WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($donor_id, $db_password);
        $stmt->fetch();

        // ✅ Since you are storing passwords in plain text
        if ($password === $db_password) {
            $_SESSION['donor_id'] = $donor_id;
            echo "success";
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "Email not found!";
    }

    $stmt->close();
    $conn->close();
}
?>
