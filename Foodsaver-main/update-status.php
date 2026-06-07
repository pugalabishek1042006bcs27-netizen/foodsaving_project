<?php
include 'db.php';
session_start();

if (!isset($_SESSION['volunteer_id'])) {
    echo "unauthorized";
    exit();
}

$food_id = $_POST['food_id'] ?? '';
$status = $_POST['status'] ?? '';

if (empty($food_id) || empty($status)) {
    echo "invalid";
    exit();
}

$sql = "UPDATE food SET status = ? WHERE food_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $food_id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}

$stmt->close();
$conn->close();
?>
