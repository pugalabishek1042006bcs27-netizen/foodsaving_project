<?php
// verify-pickup.php (robust: detects OTP column name)
session_start();
include 'db.php';

if (!isset($_SESSION['volunteer_id'])) {
    header("Location: volunteer-login.php");
    exit();
}

$volunteer_id = (int)$_SESSION['volunteer_id'];
$donation_id = (int)($_POST['donation_id'] ?? 0);
$entered = trim($_POST['otp'] ?? '');
$pickup_lat = isset($_POST['lat']) ? (float)$_POST['lat'] : null;
$pickup_lng = isset($_POST['lng']) ? (float)$_POST['lng'] : null;

if (!$donation_id || $entered === '') {
    $_SESSION['notice_type'] = 'error';
    $_SESSION['notice'] = "Invalid request.";
    header("Location: volunteer-activity.php");
    exit();
}

/*
 * Step 1: detect which OTP column exists in food_donations.
 * We check a small whitelist of possible names to avoid SQL injection.
 */
$possibleCols = ['otp_code','donor_otp','otp','code'];
$otp_col = null;

$placeholders = implode("','", array_map('addslashes', $possibleCols)); // safe list build

$sql = "
  SELECT COLUMN_NAME
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'food_donations'
    AND COLUMN_NAME IN ('$placeholders')
  LIMIT 1
";
$res = $conn->query($sql);
if ($res && $res->num_rows) {
    $row = $res->fetch_assoc();
    $otp_col = $row['COLUMN_NAME'];
}

if (!$otp_col) {
    // No OTP column found — give clear instructions and stop.
    $_SESSION['notice_type'] = 'error';
    $_SESSION['notice'] = "Server error: no OTP column found in `food_donations`. Please add one (recommended name: `otp_code`). Run: <code>ALTER TABLE food_donations ADD COLUMN otp_code VARCHAR(10) DEFAULT NULL;</code>";
    header("Location: volunteer-activity.php");
    exit();
}

/* Step 2: fetch expected OTP from the detected column and donor_id */
$stmt = $conn->prepare("SELECT {$otp_col} AS otp_val, donor_id FROM food_donations WHERE donation_id = ? AND volunteer_id = ?");
if (!$stmt) {
    $_SESSION['notice_type'] = 'error';
    $_SESSION['notice'] = "Server error: failed to prepare statement.";
    header("Location: volunteer-activity.php");
    exit();
}
$stmt->bind_param("ii", $donation_id, $volunteer_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if (!$row) {
    $_SESSION['notice_type'] = 'error';
    $_SESSION['notice'] = "Donation not found or not assigned to you.";
    header("Location: volunteer-activity.php");
    exit();
}

$expected = (string)($row['otp_val'] ?? '');
$donor_id = (int)($row['donor_id'] ?? 0);

/* Step 3: compare OTPs (string compare, preserves leading zeros if any) */
if ($expected === '') {
    $_SESSION['notice_type'] = 'error';
    $_SESSION['notice'] = "No OTP configured for this donation. Ask donor to re-upload or admin to set OTP.";
    header("Location: volunteer-activity.php");
    exit();
}

if ($expected !== $entered) {
    $_SESSION['notice_type'] = 'error';
    $_SESSION['notice'] = "❌ Wrong OTP entered. Please verify with donor.";
    header("Location: volunteer-activity.php");
    exit();
}

/* Step 4: optional pickup photo handling */
$pickup_photo_path = null;
if (isset($_FILES['pickup_photo']) && $_FILES['pickup_photo']['error'] === UPLOAD_ERR_OK) {
    $f = $_FILES['pickup_photo'];
    $allowed = ['image/jpeg','image/png'];
    if (!in_array($f['type'], $allowed) || $f['size'] > 3 * 1024 * 1024) {
        $_SESSION['notice_type'] = 'error';
        $_SESSION['notice'] = "Invalid pickup photo. Use JPEG/PNG under 3MB.";
        header("Location: volunteer-activity.php");
        exit();
    }
    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
    $fn = 'uploads/pickup_' . $donation_id . '_' . time() . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], __DIR__ . '/' . $fn)) {
        $_SESSION['notice_type'] = 'error';
        $_SESSION['notice'] = "Photo upload failed.";
        header("Location: volunteer-activity.php");
        exit();
    }
    $pickup_photo_path = $fn;
}

/* Step 5: update delivery_log (no OTP column here) and change donation status */
$conn->begin_transaction();
try {
    // Update delivery_log: mark donor_verified, set photo and location (delivery_log must exist)
    $u = $conn->prepare("UPDATE delivery_log
        SET donor_verified = 1, pickup_photo = ?, pickup_lat = ?, pickup_lng = ?, delivery_status = 'Picked Up', picked_at = NOW()
        WHERE donation_id = ? AND volunteer_id = ?");
    $u->bind_param("sddii", $pickup_photo_path, $pickup_lat, $pickup_lng, $donation_id, $volunteer_id);
    $u->execute();

    // Update donation status to in_progress
    $u2 = $conn->prepare("UPDATE food_donations SET status = 'in_progress' WHERE donation_id = ? AND volunteer_id = ?");
    $u2->bind_param("ii", $donation_id, $volunteer_id);
    $u2->execute();

    // Notify donor
    if ($donor_id) {
        $msg = "Your donation (ID: $donation_id) has been picked up by a volunteer.";
        $n = $conn->prepare("INSERT INTO notifications (user_type, user_id, message) VALUES ('donor', ?, ?)");
        $n->bind_param("is", $donor_id, $msg);
        $n->execute();
    }

    $conn->commit();
    $_SESSION['notice_type'] = 'success';
    $_SESSION['notice'] = "✅ Pickup verified successfully! You can now complete the delivery.";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['notice_type'] = 'error';
    // expose only a short message to user; keep full error for logs if needed
    $_SESSION['notice'] = "Server error during pickup verification.";
    // optional: error log (uncomment for debugging)
    // error_log("verify-pickup error: " . $e->getMessage());
}

header("Location: volunteer-activity.php");
exit();
?>
