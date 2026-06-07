<?php
session_start();
include 'db.php';

if (!isset($_SESSION['volunteer_id'])) {
    header("Location: volunteer-login.php");
    exit();
}

$volunteer_id = (int)$_SESSION['volunteer_id'];
$volunteer_name = $_SESSION['volunteer_name'] ?? 'Volunteer';

// Capture notices from other scripts
$notice = $_SESSION['notice'] ?? null;
unset($_SESSION['notice']);

/*-------------------- ACCEPT DONOR DONATION --------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'accept_donation') {
    $donation_id = intval($_POST['donation_id']);

    $check = $conn->prepare("SELECT otp_code, status FROM food_donations WHERE donation_id = ?");
    $check->bind_param("i", $donation_id);
    $check->execute();
    $res = $check->get_result()->fetch_assoc();

    if ($res && $res['status'] === 'pending') {
        $otp = $res['otp_code'];
        $stmt = $conn->prepare("UPDATE food_donations SET volunteer_id=?, status='accepted' WHERE donation_id=?");
        $stmt->bind_param("ii", $volunteer_id, $donation_id);
        $stmt->execute();
    } else {
        $_SESSION['notice'] = "⚠️ This donation is no longer available or already accepted.";
    }

    header("Location: volunteer-activity.php");
    exit();
}

/*-------------------- ACCEPT RECEIVER REQUEST --------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'accept_request') {
    $request_id = intval($_POST['request_id']);
    $stmt = $conn->prepare("UPDATE food_requests SET volunteer_id=?, status='Approved' WHERE request_id=? AND status='Pending'");
    $stmt->bind_param("ii", $volunteer_id, $request_id);
    $stmt->execute();

    $_SESSION['notice'] = "✅ Receiver request accepted.";
    header("Location: volunteer-activity.php");
    exit();
}

/*-------------------- FETCH AVAILABLE DONATIONS --------------------*/
$pending_donations = $conn->query("
    SELECT f.donation_id, f.food_type, f.quantity, d.name AS donor_name, d.contact_number, f.otp_code
    FROM food_donations f
    JOIN donor d ON f.donor_id = d.donor_id
    WHERE f.status = 'pending'
    ORDER BY f.upload_date DESC
");

/*-------------------- FETCH RECEIVER REQUESTS --------------------*/
$pending_requests = $conn->query("
    SELECT r.request_id, r.details, r.quantity, rc.receiver_name AS receiver_name
    FROM food_requests r
    JOIN receivers rc ON r.receiver_id = rc.receiver_id
    WHERE r.status = 'Pending'
    ORDER BY r.request_date DESC
");

/*-------------------- FETCH MY DELIVERIES --------------------*/
$stmt = $conn->prepare("
    SELECT f.donation_id, f.food_type, f.quantity, f.status, f.otp_code
    FROM food_donations f
    WHERE f.volunteer_id = ?
    ORDER BY f.upload_date DESC
");
$stmt->bind_param("i", $volunteer_id);
$stmt->execute();
$my_deliveries = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Volunteer Dashboard - FoodSaver</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  :root {
      --brand:#198754;
      --accent:#20c997;
  }
  body {
      background:#f8fff9;
      font-family:'Poppins',sans-serif;
  }
  .topbar {
      background:linear-gradient(90deg,var(--brand),var(--accent));
      color:#fff;
      padding:16px;
  }
  .card {
      border:none;
      border-radius:12px;
      box-shadow:0 0 15px rgba(0,0,0,0.08);
  }
  .status-pending{color:#6c757d;}
  .status-accepted{color:#0d6efd;}
  .status-in_progress{color:#fd7e14;}
  .status-completed{color:#198754;}
  .nav-link {
      color:white !important;
      font-weight:500;
  }
  .nav-link:hover {
      text-decoration:underline;
  }
</style>
</head>
<body>

<!-- ✅ TOPBAR with Dashboard link -->
<header class="topbar d-flex justify-content-between align-items-center">
  <h5 class="m-0">FoodSaver - Volunteer Panel</h5>
  <div class="d-flex align-items-center">
    <a href="volunteer-dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
    <span>Hi, <b><?= htmlspecialchars($volunteer_name) ?></b></span>
    <a href="logout.php" class="btn btn-light btn-sm ms-2">Logout</a>
  </div>
</header>

<main class="container my-4">
<?php if($notice): ?>
  <div class="alert alert-info text-center"><?=$notice?></div>
<?php endif; ?>

<div class="row g-4">
  <!-- DONATIONS -->
  <div class="col-lg-6">
    <div class="card p-3">
      <h5 class="text-success mb-3">Available Donations (Donors)</h5>
      <table class="table table-sm align-middle">
        <thead><tr><th>ID</th><th>Food</th><th>Qty</th><th>Donor</th><th>Action</th></tr></thead>
        <tbody>
        <?php if($pending_donations->num_rows): while($r=$pending_donations->fetch_assoc()): ?>
          <tr>
            <td><?=$r['donation_id']?></td>
            <td><?=$r['food_type']?></td>
            <td><?=$r['quantity']?></td>
            <td><?=$r['donor_name']?><br><small><?=$r['contact_number']?></small></td>
            <td>
              <form method="post">
                <input type="hidden" name="donation_id" value="<?=$r['donation_id']?>">
                <button class="btn btn-success btn-sm" name="action" value="accept_donation">Accept</button>
              </form>
            </td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="5" class="text-muted text-center">No pending donations.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- RECEIVER REQUESTS -->
  <div class="col-lg-6">
    <div class="card p-3">
      <h5 class="text-warning mb-3">Receiver Food Requests</h5>
      <table class="table table-sm align-middle">
        <thead><tr><th>ID</th><th>Receiver</th><th>Qty</th><th>Details</th><th>Action</th></tr></thead>
        <tbody>
        <?php if($pending_requests->num_rows): while($r=$pending_requests->fetch_assoc()): ?>
          <tr>
            <td><?=$r['request_id']?></td>
            <td><?=$r['receiver_name']?></td>
            <td><?=$r['quantity']?></td>
            <td><?=htmlspecialchars($r['details'])?></td>
            <td>
              <form method="post">
                <input type="hidden" name="request_id" value="<?=$r['request_id']?>">
                <button class="btn btn-success btn-sm" name="action" value="accept_request">Accept</button>
              </form>
            </td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="5" class="text-center text-muted">No receiver requests.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- MY DELIVERIES -->
<div class="card p-3 mt-4">
  <h5 class="text-primary mb-3">My Deliveries</h5>
  <?php if($my_deliveries->num_rows): while($d=$my_deliveries->fetch_assoc()): ?>
    <div class="border rounded p-3 mb-3">
      <strong>#<?=$d['donation_id']?> — <?=$d['food_type']?></strong>
      <div class="small mb-2">Qty: <?=$d['quantity']?> |
        Status: <b class="status-<?=$d['status']?>"><?=ucwords(str_replace('_',' ',$d['status']))?></b>
      </div>

      <?php if($d['status']==='accepted'): ?>
        <form action="verify-pickup.php" method="post" enctype="multipart/form-data">
          <input type="hidden" name="donation_id" value="<?=$d['donation_id']?>">
          <input name="otp" class="form-control form-control-sm mb-2" placeholder="Enter Donor OTP" required>
          <button class="btn btn-warning btn-sm w-100">Verify Pickup</button>
        </form>
      <?php elseif($d['status']==='in_progress'): ?>
        <form action="verify-delivery.php" method="post" enctype="multipart/form-data">
          <input type="hidden" name="donation_id" value="<?=$d['donation_id']?>">
          <input name="otp" class="form-control form-control-sm mb-2" placeholder="Enter Receiver OTP" required>
          <button class="btn btn-success btn-sm w-100">Complete Delivery</button>
        </form>
      <?php elseif($d['status']==='completed'): ?>
        <div class="text-success">✅ Delivered Successfully</div>
      <?php endif; ?>
    </div>
  <?php endwhile; else: ?>
    <p class="text-muted">No assigned deliveries yet.</p>
  <?php endif; ?>
</div>
</main>
</body>
</html>
