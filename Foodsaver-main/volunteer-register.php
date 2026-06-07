<?php
include 'db.php';

// Get form data safely
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$contact_number = trim($_POST['contact_number'] ?? '');
$region = trim($_POST['region'] ?? '');
$availability = trim($_POST['availability'] ?? '');

if (empty($name) || empty($email) || empty($password) || empty($contact_number) || empty($region) || empty($availability)) {
    echo "All fields are required.";
    exit;
}

// Check if email already exists
$check = $conn->prepare("SELECT email FROM volunteer WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo "Email already registered.";
    exit;
}
$check->close();

// Insert new volunteer
$sql = "INSERT INTO volunteer (name, email, password, contact_number, region, availability)
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $name, $email, $password, $contact_number, $region, $availability);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
