<?php
session_start();
include 'db.php';

if (!isset($_SESSION['donor_id'])) {
    header("Location: donor-login.php");
    exit();
}

$donor_id = (int)$_SESSION['donor_id'];
$donor_name = $_SESSION['donor_name'] ?? 'Donor';
$alert = null;
$generated_otp = null;

// Handle POST (upload)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize inputs
    $foodType = $conn->real_escape_string($_POST['foodType'] ?? '');
    $quantity = $conn->real_escape_string($_POST['quantity'] ?? '');
    $expiryDate = $conn->real_escape_string($_POST['expiryDate'] ?? null);
    $preparationStatus = $conn->real_escape_string($_POST['preparationStatus'] ?? '');
    $dietary = isset($_POST['dietary']) ? implode(',', array_map(fn($v) => $conn->real_escape_string($v), (array)$_POST['dietary'])) : '';
    $allergens = $conn->real_escape_string($_POST['allergens'] ?? '');
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    $contactPhone = $conn->real_escape_string($_POST['contactPhone'] ?? '');
    $contactEmail = $conn->real_escape_string($_POST['contactEmail'] ?? '');
    $address = $conn->real_escape_string($_POST['address'] ?? '');

    // handle images
    $uploadedPaths = [];
    $uploadDir = __DIR__ . '/uploads/';
    $publicDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    if (!empty($_FILES['foodImages']['name'][0])) {
        foreach ($_FILES['foodImages']['tmp_name'] as $idx => $tmp) {
            if (is_uploaded_file($tmp)) {
                $orig = basename($_FILES['foodImages']['name'][$idx]);
                $ext = pathinfo($orig, PATHINFO_EXTENSION);
                $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . ($ext ?: 'jpg');
                $dest = $uploadDir . $filename;
                if (move_uploaded_file($tmp, $dest)) {
                    $uploadedPaths[] = $publicDir . $filename;
                }
            }
        }
    }

    $imageList = $conn->real_escape_string(implode(',', $uploadedPaths));

    // ✅ Generate 4-digit OTP
    $generated_otp = strval(rand(1000, 9999));

    // ✅ Insert donation including otp_code
    $stmt = $conn->prepare("INSERT INTO food_donations 
        (donor_id, food_type, quantity, expiry_date, preparation_status, dietary_options, allergens, description, contact_phone, contact_email, address, image_paths, otp_code, status, upload_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("issssssssssss", $donor_id, $foodType, $quantity, $expiryDate, $preparationStatus, $dietary, $allergens, $description, $contactPhone, $contactEmail, $address, $imageList, $generated_otp);

    if ($stmt->execute()) {
        $donation_id = $stmt->insert_id;

        // notify volunteers and receivers
        $msg = "New donation uploaded (#{$donation_id}): {$foodType} — {$quantity}";
        $n1 = $conn->prepare("INSERT INTO notifications (user_type, user_id, message) VALUES ('volunteer', 0, ?)");
        $n1->bind_param("s", $msg); $n1->execute();
        $n2 = $conn->prepare("INSERT INTO notifications (user_type, user_id, message) VALUES ('receiver', 0, ?)");
        $n2->bind_param("s", $msg); $n2->execute();

        // ✅ Success message with OTP
        $alert = ['type'=>'success','text'=>"Donation uploaded successfully! <br><strong>Your pickup OTP is: <span style='font-size:1.3rem;color:#155724'>{$generated_otp}</span></strong><br>Please share this OTP with the volunteer only."];
    } else {
        $alert = ['type'=>'danger','text'=>'Database Error: '.$stmt->error];
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Donate Food — FoodSaver</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    :root { --brand:#198754; --brand-2:#20c997; }
    body { font-family: "Poppins", sans-serif; background: linear-gradient(180deg,#f1f8f4 0%, #ffffff 60%); }
    .hero { background: linear-gradient(135deg,var(--brand),var(--brand-2)); color: #fff; padding: 28px; border-radius: 12px; box-shadow: 0 8px 30px rgba(16,128,88,0.12); }
    .card-soft { border-radius: 14px; box-shadow: 0 6px 20px rgba(6,56,35,0.06); border: none; }
    label { font-weight:600; color:#2f5d45; }
    .submit-btn { background: linear-gradient(90deg,var(--brand),var(--brand-2)); color:#fff; border: none; padding: 12px 22px; border-radius:10px; }
    .submit-btn:active{ transform: translateY(1px); }
    .small-muted { color:#6c757d; font-size:0.9rem; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light py-3 mb-4">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="donor-dashboard.php">
      <div class="me-2"><i class="fas fa-leaf fa-2x" style="color:var(--brand)"></i></div>
      <div>
        <strong style="color:#134d36">FOODSAVER</strong>
        <div class="small-muted">Donate • Connect • Feed</div>
      </div>
    </a>

    <div class="ms-auto d-flex align-items-center gap-3">
      <!-- ✅ Added Dashboard Button -->
      <a class="btn btn-success btn-sm" href="donor-dashboard.php" 
         style="background: linear-gradient(90deg,var(--brand),var(--brand-2)); border:none;">
         <i class="fas fa-home me-1"></i> Dashboard
      </a>

      <!-- Welcome text -->
     <span>Welcome, <strong><?= htmlspecialchars($donor_name) ?></strong></span>

      <!-- Logout -->
      <a class="btn btn-outline-secondary btn-sm" href="logout.php">Logout</a>
    </div>
  </div>
</nav>


<main class="container mb-5">
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="hero p-4 mb-3">
        <h3 class="mb-1">Share a meal — help your community</h3>
        <p class="mb-0 small-muted">Upload details of your food donation. Volunteers and receivers nearby will be notified automatically.</p>
      </div>

      <div class="card card-soft p-4">
        <?php if($alert): ?>
          <div class="alert alert-<?=$alert['type']?>"><?=$alert['text']?></div>
        <?php endif; ?>

        <form id="donorForm" method="post" enctype="multipart/form-data">
          <div class="row g-3">
            <div class="col-md-6">
              <label>Food Type*</label>
              <select name="foodType" class="form-select" required>
                <option value="">Select</option>
                <option value="Cooked Meals">Cooked Meals</option>
                <option value="Fruits & Vegetables">Fruits & Vegetables</option>
                <option value="Baked Goods">Baked Goods</option>
                <option value="Dairy Products">Dairy Products</option>
                <option value="Canned Items">Canned Items</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <div class="col-md-3"><label>Quantity*</label><input name="quantity" class="form-control" required></div>
            <div class="col-md-3"><label>Safe Date*</label><input type="date" name="expiryDate" class="form-control" required></div>

            <div class="col-md-6">
              <label>Preparation Status*</label>
              <select name="preparationStatus" class="form-select" required>
                <option value="">Select</option>
                <option value="Ready to Eat">Ready to Eat</option>
                <option value="Needs Reheating">Needs Reheating</option>
                <option value="Raw/Fresh">Raw/Fresh</option>
                <option value="Frozen">Frozen</option>
              </select>
            </div>

            <div class="col-md-6">
              <label>Dietary Options</label>
              <div class="d-flex gap-2">
                <label class="form-check form-check-inline"><input type="checkbox" name="dietary[]" value="Vegetarian" class="form-check-input"> <span class="form-check-label">Vegetarian</span></label>
                <label class="form-check form-check-inline"><input type="checkbox" name="dietary[]" value="Vegan" class="form-check-input"> <span class="form-check-label">Mixed</span></label>
                <label class="form-check form-check-inline"><input type="checkbox" name="dietary[]" value="Non-Veg" class="form-check-input"> <span class="form-check-label">Non-Veg</span></label>
              </div>
            </div>

            <div class="col-12"><label>Potential Allergens*</label><textarea name="allergens" class="form-control" rows="2" required></textarea></div>
            <div class="col-12"><label>Description*</label><textarea name="description" class="form-control" rows="3" required></textarea></div>

            <div class="col-md-6"><label>Contact Phone*</label><input name="contactPhone" class="form-control" maxlength="10" required></div>
            <div class="col-md-6"><label>Email (optional)</label><input type="email" name="contactEmail" class="form-control"></div>

            <div class="col-12"><label>Pickup Address*</label><input name="address" class="form-control" required></div>

            <div class="col-12"><label>Food Images (max 5MB)</label><input type="file" name="foodImages[]" accept="image/*" multiple class="form-control" required></div>

            <div class="col-12 text-end">
              <button type="submit" class="submit-btn" id="submitBtn">
                <span id="submitText">Submit Donation</span>
                <span id="loadingSpinner" class="loading" style="display:none"></span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card card-soft p-3 mb-3">
        <h5 class="mb-2">Why donate?</h5>
        <p class="small-muted mb-0">Your donation helps reduce food waste and feeds someone in need. Volunteers pick up and deliver safely.</p>
      </div>

      <div class="card card-soft p-3">
        <h6 class="mb-2">Tips</h6>
        <ul class="small-muted mb-0">
          <li>Pack cooked food in closed containers.</li>
          <li>Label temperature-sensitive items.</li>
          <li>Upload clear photos to help volunteers decide.</li>
        </ul>
      </div>
    </div>
  </div>
</main>

<footer class="text-center py-3" style="background:linear-gradient(90deg,var(--brand),var(--brand-2)); color:#fff;">
  © <?=date('Y')?> FoodSaver — Built with ❤ for communities
</footer>
</body>
</html>
