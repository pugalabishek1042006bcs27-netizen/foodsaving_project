<?php
session_start();
include 'db.php';

if (!isset($_SESSION['receiver_id'])) {
    header("Location: receiver-login.php");
    exit();
}

$receiver_id = (int)$_SESSION['receiver_id'];
$receiver_name = $_SESSION['receiver_name'] ?? 'Receiver';
$msg = null;

/* =========================================================
   1️⃣ Request food (from available donations list)
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_food'])) {
    if (!empty($_POST['donation_id'])) {
        $donation_id = (int)$_POST['donation_id'];
        $quantity = (int)($_POST['quantity'] ?? 1);
        $details = trim($_POST['details'] ?? 'Requested from donor list');

        $receiver_otp = rand(1000, 9999); // Generate OTP

        $chk = $conn->prepare("SELECT donation_id FROM food_donations WHERE donation_id=? LIMIT 1");
        $chk->bind_param("i", $donation_id);
        $chk->execute();
        $res = $chk->get_result();

        if ($res && $res->num_rows > 0) {
            $stmt = $conn->prepare("
                INSERT INTO food_requests (receiver_id, donation_id, quantity, details, receiver_otp)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iiiss", $receiver_id, $donation_id, $quantity, $details, $receiver_otp);
            if ($stmt->execute()) {
                $msg = "✅ Request sent successfully! Your delivery OTP: <strong>$receiver_otp</strong>";
            } else {
                $msg = "❌ Database Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $msg = "❌ The selected donation no longer exists.";
        }
        $chk->close();
    } else {
        $msg = "❌ Missing donation information.";
    }
}

/* =========================================================
   2️⃣ Manual request (raised directly by receiver)
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['raise_request'])) {
    $quantity = (int)($_POST['quantity'] ?? 1);
    $details = trim($_POST['details'] ?? 'Manual request');
    $receiver_otp = rand(1000, 9999);

    $stmt = $conn->prepare("
        INSERT INTO food_requests (receiver_id, donation_id, quantity, details, receiver_otp)
        VALUES (?, NULL, ?, ?, ?)
    ");
    $stmt->bind_param("iiss", $receiver_id, $quantity, $details, $receiver_otp);
    if ($stmt->execute()) {
        $msg = "✅ Requirement raised! Your delivery OTP: <strong>$receiver_otp</strong>";
    } else {
        $msg = "❌ Database Error: " . $stmt->error;
    }
    $stmt->close();
}

/* =========================================================
   3️⃣ Fetch available donations
   ========================================================= */
$res = $conn->query("
    SELECT f.*, d.name AS donor_name 
    FROM food_donations f 
    LEFT JOIN donor d ON f.donor_id = d.donor_id 
    WHERE f.status IN ('pending', 'accepted')
    ORDER BY f.upload_date DESC
");

/* =========================================================
   4️⃣ Fetch accepted donations (show OTP)
   ========================================================= */
$myDeliveries = $conn->query("
    SELECT f.*, d.name AS donor_name, v.name AS volunteer_name, r.receiver_otp
    FROM food_donations f
    LEFT JOIN donor d ON f.donor_id = d.donor_id
    LEFT JOIN volunteer v ON f.volunteer_id = v.volunteer_id
    LEFT JOIN food_requests r ON f.donation_id = r.donation_id
    WHERE r.receiver_id = $receiver_id
    ORDER BY f.upload_date DESC
");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Receiver Panel — FoodSaver</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
  <style>
    :root{ --brand:#198754; --brand-2:#20c997; --accent:#20c997; }
    body{ font-family:'Poppins',sans-serif; background:#f4fbf6; }
    .card-food{ border-radius:12px; box-shadow:0 6px 18px rgba(10,70,40,0.06);
                border:none; overflow:hidden; }
    .food-img{ width:120px; height:100px; object-fit:cover; border-radius:8px; }
    .small-muted{ color:#6c757d; }
    .otp-box{ background:#e8f9f1; padding:8px 12px; border-radius:8px; display:inline-block; font-weight:600; color:#155d36; }
  </style>
</head>
<body>

<!-- ✅ Updated Receiver Navbar -->
<nav class="navbar navbar-expand-lg navbar-light py-3 mb-4" style="box-shadow:0 3px 10px rgba(0,0,0,0.1);">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="receiver-dashboard.php">
      <div class="me-2"><i class="fas fa-leaf fa-2x" style="color:var(--brand)"></i></div>
      <div>
        <strong style="color:#134d36">FOODSAVER</strong>
        <div class="small-muted">Receive • Connect • Feed</div>
      </div>
    </a>

    <div class="ms-auto d-flex align-items-center gap-3">
      <a class="btn btn-success btn-sm" href="receiver-dashboard.php"
         style="background: linear-gradient(90deg,var(--brand),var(--brand-2)); border:none;">
         <i class="fas fa-home me-1"></i> Dashboard
      </a>

      <span>Welcome, <strong><?=htmlspecialchars($receiver_name)?></strong></span>

      <a class="btn btn-outline-secondary btn-sm" href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<main class="container mb-5">
  <?php if($msg): ?><div class="alert alert-info"><?=$msg?></div><?php endif; ?>

  <div class="row g-4">
    <!-- ✅ LEFT SIDE: Available Donations -->
    <div class="col-lg-8">
      <h4 class="mb-3 text-success">Available Donations</h4>

      <?php if ($res && $res->num_rows): while($row = $res->fetch_assoc()):
        $imagePath = 'uploads/default_food.png';
        if (!empty($row['image_paths'])) {
          $images = explode(',', $row['image_paths']);
          $first = trim($images[0]);
          if (file_exists(__DIR__.'/'.$first)) $imagePath = $first;
          elseif (file_exists(__DIR__.'/uploads/'.basename($first))) $imagePath = 'uploads/'.basename($first);
        }
      ?>
        <div class="card card-food mb-3 p-3">
          <div class="d-flex gap-3 align-items-center">
            <img src="<?=$imagePath?>" class="food-img" onerror="this.src='uploads/default_food.png'">
            <div class="flex-grow-1">
              <h5 class="mb-1">
                <?=htmlspecialchars($row['food_type'])?> 
                <small class="text-muted">by <?=htmlspecialchars($row['donor_name'])?></small>
              </h5>
              <p class="mb-1 small-muted">
                <strong>Qty:</strong> <?=htmlspecialchars($row['quantity'])?> &nbsp; 
                <strong>Expiry:</strong> <?=htmlspecialchars($row['expiry_date'])?>
              </p>
              <p class="mb-0"><?=htmlspecialchars(substr($row['description'],0,200))?></p>
              <div class="mt-2">
                <form method="POST" class="d-inline">
                  <input type="hidden" name="donation_id" value="<?=htmlspecialchars($row['donation_id'],ENT_QUOTES)?>">
                  <input type="hidden" name="quantity" value="<?=htmlspecialchars($row['quantity'],ENT_QUOTES)?>">
                  <button name="request_food" class="btn btn-success btn-sm">Request Food</button>
                </form>
                <a class="btn btn-outline-secondary btn-sm" target="_blank"
                   href="https://www.google.com/maps/search/?api=1&query=<?=urlencode($row['address'])?>">
                   View Pickup
                </a>
              </div>
            </div>
            <div class="text-end small-muted">
              <div>Uploaded</div>
              <div><?=htmlspecialchars($row['upload_date'] ?? '')?></div>
            </div>
          </div>
        </div>
      <?php endwhile; else: ?>
        <p class="text-center text-muted">No donations available right now.</p>
      <?php endif; ?>

      <hr class="my-4">
      <h4 class="mb-3 text-success">My Accepted Donations</h4>

      <?php if ($myDeliveries && $myDeliveries->num_rows): while($row = $myDeliveries->fetch_assoc()): ?>
        <div class="card card-food mb-3 p-3">
          <div class="d-flex justify-content-between">
            <div>
              <h5><?=htmlspecialchars($row['food_type'])?></h5>
              <p class="mb-1 small-muted">Volunteer: <?=htmlspecialchars($row['volunteer_name'] ?? 'Pending')?></p>
              <p class="mb-1 small-muted">Status: <?=htmlspecialchars($row['status'])?></p>
              <?php if (!empty($row['receiver_otp'])): ?>
                <p class="mb-0">Your Delivery OTP: <span class="otp-box"><?=htmlspecialchars($row['receiver_otp'])?></span></p>
              <?php else: ?>
                <p class="mb-0 text-muted">OTP not generated yet.</p>
              <?php endif; ?>
            </div>
            <div class="text-end small-muted">
              <div>Uploaded</div>
              <div><?=htmlspecialchars($row['upload_date'] ?? '')?></div>
            </div>
          </div>
        </div>
      <?php endwhile; else: ?>
        <p class="text-center text-muted">No active or completed deliveries yet.</p>
      <?php endif; ?>
    </div>

    <!-- ✅ RIGHT SIDE: Raise a Request -->
    <div class="col-lg-4">
      <div class="card p-3 mb-3">
        <h5 class="text-success">Raise a Request</h5>
        <form method="POST">
          <div class="mb-2">
            <input class="form-control" name="quantity" placeholder="Quantity" required>
          </div>
          <div class="mb-2">
            <textarea class="form-control" name="details" placeholder="Details (optional)" rows="3"></textarea>
          </div>
          <div class="text-end">
            <button name="raise_request" class="btn btn-success">Raise Request</button>
          </div>
        </form>
      </div>

      <div class="card p-3">
        <h6 class="mb-2">Why raise?</h6>
        <p class="small-muted mb-0">
          Raising requests helps volunteers prioritize urgent needs in your area.
          Be specific in quantity and location.
        </p>
      </div>
    </div>
  </div>
</main>

<footer class="text-center py-3"
        style="background:linear-gradient(90deg,var(--brand),var(--accent)); color:#fff;">
  © <?=date('Y')?> FoodSaver
</footer>
</body>
</html>
