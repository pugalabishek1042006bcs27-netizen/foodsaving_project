<?php
include 'db.php'; // Database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $contact = $_POST['contact_number'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $address = $_POST['address'] ?? '';

    if (!$name || !$contact || !$email || !$password || !$address) {
        echo "All fields are required!";
        exit;
    }

    // ✅ Check if donor email already exists
    $stmt = $conn->prepare("SELECT donor_id FROM donor WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Email already registered!";
        exit;
    }
    $stmt->close();

    // ✅ Insert new donor (store password in plain text as per your instruction)
    $stmt = $conn->prepare("INSERT INTO donor (name, contact_number, email, password, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $contact, $email, $password, $address);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Database error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
