<?php
// receiver-register.php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid request";
    exit;
}

// Read input fields
$org_name = trim($_POST['name'] ?? '');
$receiver_name = trim($_POST['contact_person'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = trim($_POST['password'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$state = trim($_POST['state'] ?? '');
$pincode = trim($_POST['pincode'] ?? '');

// Validation
if ($org_name === '' || $receiver_name === '' || $email === '' || $password === '' || $address === '') {
    echo "All required fields must be filled";
    exit;
}

// Check duplicate email
$check = $conn->prepare("SELECT receiver_id FROM receivers WHERE email = ?");
if (!$check) {
    echo "Database error: " . $conn->error;
    exit;
}
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "Email already registered!";
    $check->close();
    $conn->close();
    exit;
}
$check->close();

// Insert into receivers
$stmt = $conn->prepare("
    INSERT INTO receivers (org_name, receiver_name, email, phone, password, address, city, state, pincode)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
if (!$stmt) {
    echo "Database error: " . $conn->error;
    exit;
}

$stmt->bind_param("sssssssss", $org_name, $receiver_name, $email, $phone, $password, $address, $city, $state, $pincode);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "Database Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
