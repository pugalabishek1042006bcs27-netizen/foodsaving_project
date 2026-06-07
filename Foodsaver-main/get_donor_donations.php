<?php
session_start();
include 'db.php';

$donor_id = $_SESSION['donor_id'];

$sql = "SELECT donation_id, food_type, quantity, otp_code, status, expiry_date, upload_date 
        FROM food_donations 
        WHERE donor_id = ? 
        ORDER BY upload_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$result = $stmt->get_result();

$donations = [];
while ($row = $result->fetch_assoc()) {
    $donations[] = $row;
}

echo json_encode($donations);
?>
