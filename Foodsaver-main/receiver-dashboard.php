<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['receiver_id'])) {
    header("Location: receiver-login.php");
    exit();
}

$receiver_id = $_SESSION['receiver_id'];

// ✅ Fetch receiver details freshly
$stmt = $conn->prepare("SELECT receiver_name, email, address, city, state, pincode, profile_picture FROM receivers WHERE receiver_id = ?");
$stmt->bind_param("i", $receiver_id);
$stmt->execute();
$result = $stmt->get_result();
$receiver = $result->fetch_assoc();
$stmt->close();

$_SESSION['receiver_name'] = $receiver['receiver_name'];
$name = $receiver['receiver_name'];

// ✅ Handle certificate upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['certificate'])) {
    $file_name = $_FILES['certificate']['name'];
    $file_tmp = $_FILES['certificate']['tmp_name'];
    $upload_dir = "uploads/certificates/";

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $target = $upload_dir . basename($file_name);
    move_uploaded_file($file_tmp, $target);

    // Insert record into certificates table
    $stmt = $conn->prepare("INSERT INTO certificates (receiver_id, file_path, status) VALUES (?, ?, 'Pending')");
    $stmt->bind_param("is", $receiver_id, $file_name);
    $stmt->execute();
    $stmt->close();

    $msg = "✅ Certificate uploaded successfully and awaiting admin approval.";
}

// ✅ Check latest certificate status
$cert = $conn->query("SELECT * FROM certificates WHERE receiver_id=$receiver_id ORDER BY uploaded_at DESC LIMIT 1");
$status = "Not Uploaded";
if ($cert->num_rows > 0) {
    $row = $cert->fetch_assoc();
    $status = $row['status'];
}

// ✅ Restrict unauthorized access (backend-level)
if ($status !== 'Approved') {
    $restricted_pages = ['receiver-function.php', 'volunteer-activity.php'];
    if (in_array(basename($_SERVER['PHP_SELF']), $restricted_pages)) {
        header("Location: receiver-dashboard.php?error=not_approved");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Receiver Dashboard | FoodSaver</title>
<style>
body {
    font-family: "Poppins", sans-serif;
    background: #f6f9f6;
    margin: 0;
}
.navbar {
    background: #2b8a3e;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 40px;
}
.navbar .logo {
    font-size: 22px;
    font-weight: bold;
}
.navbar nav a {
    color: white;
    text-decoration: none;
    margin-left: 25px;
    font-weight: 500;
}
.navbar nav a:hover {
    text-decoration: underline;
}
.container {
    max-width: 850px;
    margin: 40px auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.1);
}
.profile {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}
.profile img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 20px;
    border: 3px solid #2b8a3e;
}
.profile-info {
    flex: 1;
}
.profile-info h3 {
    color: #2b8a3e;
    margin: 0;
}
.profile-info p {
    margin: 4px 0;
}
h2 {
    color: #2b8a3e;
    text-align: center;
}
input[type=file], button {
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
}
button {
    background: #2b8a3e;
    color: white;
    font-weight: bold;
    cursor: pointer;
}
button:hover {
    background: #256c31;
}
.status-box {
    background: #f1fff1;
    padding: 15px;
    border-left: 5px solid #2b8a3e;
    margin-top: 20px;
}
.message {
    background: #e8ffe8;
    padding: 10px;
    border-radius: 6px;
    color: #2b8a3e;
    margin-bottom: 15px;
    text-align: center;
}
.error {
    background: #ffeaea;
    padding: 10px;
    border-radius: 6px;
    color: #d63b3b;
    margin-bottom: 15px;
    text-align: center;
}
.edit-btn {
    display: inline-block;
    background: #2b8a3e;
    color: white;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: bold;
}
.edit-btn:hover {
    background: #256c31;
}
</style>
</head>
<body>

<header>
  <div class="navbar">
    <div class="logo">FoodSaver | Receiver Panel</div>
    <nav>
      <a href="receiver-dashboard.php">Dashboard</a>
      <?php if ($status === 'Approved') { ?>
          <a href="receiver-function.php">Food Section</a>
      <?php } else { ?>
          <a href="#" style="opacity:0.6; pointer-events:none;" title="Certificate not approved yet">Food Section</a>
      <?php } ?>
      <a href="logout.php" style="color:#ffdddd;">Logout</a>
    </nav>
  </div>
</header>

<div class="container">
  <h2>Welcome, <?= htmlspecialchars($name) ?> 👋</h2>

  <?php
  if(isset($msg)) echo "<div class='message'>$msg</div>";
  if(isset($_GET['error']) && $_GET['error'] === 'not_approved') {
      echo "<div class='error'>⚠️ You cannot access activity or food sections until your certificate is approved by the admin.</div>";
  }
  ?>

  <div class="profile">
      <img src="<?= !empty($receiver['profile_picture']) ? 'uploads/profile/'.$receiver['profile_picture'] : 'images/default-avatar.png' ?>" alt="Profile Picture">
      <div class="profile-info">
          <h3><?= htmlspecialchars($receiver['receiver_name']) ?></h3>
          <p><b>Email:</b> <?= htmlspecialchars($receiver['email']) ?></p>
          <p><b>Address:</b> <?= htmlspecialchars($receiver['address']) ?>, <?= htmlspecialchars($receiver['city']) ?>, <?= htmlspecialchars($receiver['state']) ?> - <?= htmlspecialchars($receiver['pincode']) ?></p>
          <a href="update-receiver-profile.php" class="edit-btn">Edit Profile</a>
      </div>
  </div>

  <form method="POST" enctype="multipart/form-data">
      <label>Upload Government Approved Certificate:</label>
      <input type="file" name="certificate" required>
      <button type="submit">Upload Certificate</button>
  </form>

  <div class="status-box">
      <h3>Certificate Status: <?= htmlspecialchars($status) ?></h3>
      <?php if ($status === 'Approved') { ?>
          <form action="volunteer-activity.php" method="get">
              <button type="submit">Go to Volunteer Activity</button>
          </form>
      <?php } else { ?>
          <p style="color:#d63b3b;">⚠️ You can access volunteer activities once your certificate is approved by the admin.</p>
      <?php } ?>
  </div>
</div>

</body>
</html>
