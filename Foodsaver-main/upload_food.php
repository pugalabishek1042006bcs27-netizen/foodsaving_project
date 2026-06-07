<?php
// upload_food.php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['donor_id'])) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$donor_id = (int)$_SESSION['donor_id'];

// sanitize inputs (basic)
$foodType = $conn->real_escape_string($_POST['foodType'] ?? '');
$quantity = $conn->real_escape_string($_POST['quantity'] ?? '');
$expiryDate = $conn->real_escape_string($_POST['expiryDate'] ?? null);
$preparationStatus = $conn->real_escape_string($_POST['preparationStatus'] ?? '');
$dietary = isset($_POST['dietary']) ? implode(',', array_map(function($v) use ($conn){return $conn->real_escape_string($v);}, (array)$_POST['dietary'])) : '';
$allergens = $conn->real_escape_string($_POST['allergens'] ?? '');
$description = $conn->real_escape_string($_POST['description'] ?? '');
$contactPhone = $conn->real_escape_string($_POST['contactPhone'] ?? '');
$contactEmail = $conn->real_escape_string($_POST['contactEmail'] ?? '');
$address = $conn->real_escape_string($_POST['address'] ?? '');

// images upload
$imagePaths = [];
$uploadDir = __DIR__ . '/uploads/';
$publicDir = 'uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if (!empty($_FILES['foodImages']['name'][0])) {
    foreach ($_FILES['foodImages']['tmp_name'] as $idx => $tmp) {
        if (is_uploaded_file($tmp)) {
            $orig = basename($_FILES['foodImages']['name'][$idx]);
            $ext = pathinfo($orig, PATHINFO_EXTENSION);
            $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest = $uploadDir . $filename;
            if (move_uploaded_file($tmp, $dest)) {
                $imagePaths[] = $publicDir . $filename;
            }
        }
    }
}
$imageList = $conn->real_escape_string(implode(',', $imagePaths));

// generate OTPs
$pickupOTP = strval(random_int(100000, 999999));
$deliveryOTP = strval(random_int(100000, 999999));

// insert donation
$stmt = $conn->prepare("INSERT INTO food_donations (donor_id, food_type, quantity, expiry_date, preparation, dietary, allergens, description, contact_phone, contact_email, address, image_paths, pickup_otp, delivery_otp, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'pending')");
$stmt->bind_param("issssssssssss", $donor_id, $foodType, $quantity, $expiryDate, $preparationStatus, $dietary, $allergens, $description, $contactPhone, $contactEmail, $address, $imageList, $pickupOTP, $deliveryOTP);

if ($stmt->execute()) {
    $donation_id = $stmt->insert_id;
    // Notifications: insert a broadcast message for volunteers and receivers
    $msg = "New donation uploaded (#$donation_id): $foodType — $quantity (uploaded at " . date('Y-m-d H:i:s') . ")";
    $n1 = $conn->prepare("INSERT INTO notifications (user_role, user_id, message) VALUES ('volunteer', 0, ?)");
    $n1->bind_param("s", $msg); $n1->execute();
    $n2 = $conn->prepare("INSERT INTO notifications (user_role, user_id, message) VALUES ('receiver', 0, ?)");
    $n2->bind_param("s", $msg); $n2->execute();

    // Optionally: email/SMS hooks could be added here (not included)

    echo json_encode(['status'=>'success','message'=>'Donation uploaded','donation_id'=>$donation_id]);
} else {
    echo json_encode(['status'=>'error','message'=>'DB error: '.$stmt->error]);
}
