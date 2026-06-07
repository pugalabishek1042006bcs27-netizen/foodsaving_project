<?php
session_start();
include 'db.php';

if (!isset($_SESSION['volunteer_id'])) {
    header("Location: volunteer-login.php");
    exit();
}

$volunteer_id = (int)$_SESSION['volunteer_id'];
$donation_id = (int)($_POST['donation_id'] ?? 0);
$entered = trim($_POST['otp'] ?? '');

if (!$donation_id || $entered === '') {
    $_SESSION['notice_type'] = 'error';
    $_SESSION['notice'] = "Invalid request.";
    header("Location: volunteer-activity.php");
    exit();
}

// Fetch the expected receiver OTP from food_requests
$stmt = $conn->prepare("
    SELECT r.receiver_otp, r.receiver_id
    FROM food_requests r
    JOIN food_donations f ON f.donation_id = r.donation_id
    WHERE f.donation_id=? AND f.volunteer_id=? AND r.status='Approved'
");
$stmt->bind_param("ii", $donation_id, $volunteer_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if (!$row) {
    $_SESSION['notice_type'] = 'error';
    $_SESSION['notice'] = "No matching request found for this delivery.";
    header("Location: volunteer-activity.php");
    exit();
}

$expected = $row['receiver_otp'] ?? '';
$receiver_id = $row['receiver_id'] ?? 0;

// Verify OTP
if ($expected !== $entered) {
    $_SESSION['notice_type'] = 'error';
    $_SESSION['notice'] = "❌ Wrong Receiver OTP. Please verify again.";
    header("Location: volunteer-activity.php");
    exit();
}

// Update delivery status
$conn->begin_transaction();
try {
    $u1 = $conn->prepare("UPDATE delivery_log SET delivery_status='Delivered' WHERE donation_id=? AND volunteer_id=?");
    $u1->bind_param("ii", $donation_id, $volunteer_id);
    $u1->execute();

    $u2 = $conn->prepare("UPDATE food_donations SET status='completed' WHERE donation_id=? AND volunteer_id=?");
    $u2->bind_param("ii", $donation_id, $volunteer_id);
    $u2->execute();

    $u3 = $conn->prepare("UPDATE food_requests SET status='Completed' WHERE donation_id=?");
    $u3->bind_param("i", $donation_id);
    $u3->execute();

    // Notify receiver
    $msg = "Your food request (Donation ID: $donation_id) has been delivered successfully!";
    $n = $conn->prepare("INSERT INTO notifications (user_type, user_id, message) VALUES ('receiver', ?, ?)");
    $n->bind_param("is", $receiver_id, $msg);
    $n->execute();

    $conn->commit();
    $_SESSION['notice_type'] = 'success';
    $_SESSION['notice'] = "✅ Delivery verified successfully!";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['notice_type'] = 'error';
    $_SESSION['notice'] = "Server error during delivery verification.";
}

header("Location: volunteer-activity.php");
exit();
?>
