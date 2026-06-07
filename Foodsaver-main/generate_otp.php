<?php
session_start();
include 'db.php';

// ✅ Only donors can generate OTP
if (!isset($_SESSION['donor_id'])) {
    header("Location: donor-login.php");
    exit();
}

$donor_id = $_SESSION['donor_id'];

if (isset($_POST['donation_id'])) {
    $donation_id = intval($_POST['donation_id']);

    // Check donation ownership
    $stmt = $conn->prepare("SELECT donation_id FROM food_donations WHERE donation_id=? AND donor_id=?");
    $stmt->bind_param("ii", $donation_id, $donor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $otp = rand(100000, 999999);
        $stmt2 = $conn->prepare("UPDATE food_donations SET otp_code=? WHERE donation_id=?");
        $stmt2->bind_param("si", $otp, $donation_id);
        $stmt2->execute();

        echo "<script>alert('OTP generated successfully! Your pickup OTP is: $otp'); window.location='donor-dashboard.php';</script>";
    } else {
        echo "<script>alert('Invalid donation record.'); window.location='donor-dashboard.php';</script>";
    }
}
?>
