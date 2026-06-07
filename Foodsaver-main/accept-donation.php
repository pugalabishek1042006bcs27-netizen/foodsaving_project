<?php
// accept-donation.php
session_start();
include 'db.php';
if (!isset($_SESSION['volunteer_id'])) { http_response_code(401); exit('Not logged in'); }
$volunteer_id = (int)$_SESSION['volunteer_id'];
$donation_id = (int)($_POST['donation_id'] ?? 0);
if (!$donation_id) exit('Invalid donation id');

$otp = strval(random_int(100000, 999999));

$conn->begin_transaction();
try {
    $u = $conn->prepare("UPDATE food_donations SET volunteer_id=?, status='accepted', otp_code=? WHERE donation_id=? AND status='pending'");
    $u->bind_param("isi", $volunteer_id, $otp, $donation_id);
    $u->execute();

    $ins = $conn->prepare("INSERT INTO delivery_log (donation_id, volunteer_id, donor_otp, delivery_status) VALUES (?, ?, ?, 'Assigned')");
    $ins->bind_param("iis", $donation_id, $volunteer_id, $otp);
    $ins->execute();

    // Notify donor: need donor_id
    $q = $conn->prepare("SELECT donor_id FROM food_donations WHERE donation_id=?");
    $q->bind_param("i",$donation_id); $q->execute(); $d = $q->get_result()->fetch_assoc();
    $donor_id = $d['donor_id'] ?? 0;
    $msg = "Volunteer has accepted your donation (ID: $donation_id). Pickup OTP: $otp";

    $n = $conn->prepare("INSERT INTO notifications (user_type, user_id, message) VALUES ('donor', ?, ?)");
    $n->bind_param("is", $donor_id, $msg);
    $n->execute();

    $conn->commit();
    echo json_encode(['status'=>'ok','otp'=>$otp]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status'=>'error','error'=>$e->getMessage()]);
}
