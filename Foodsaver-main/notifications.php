<?php
session_start();
include 'db.php';

// Determine user type
if (isset($_SESSION['volunteer_id'])) {
    $user_type = 'volunteer';
    $user_id = $_SESSION['volunteer_id'];
} elseif (isset($_SESSION['donor_id'])) {
    $user_type = 'donor';
    $user_id = $_SESSION['donor_id'];
} elseif (isset($_SESSION['receiver_id'])) {
    $user_type = 'receiver';
    $user_id = $_SESSION['receiver_id'];
} else {
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT message, created_at FROM notifications WHERE user_type=? AND user_id=? ORDER BY created_at DESC");
$stmt->bind_param("si", $user_type, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Notifications | FoodSaver</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f9fafb; font-family: 'Poppins', sans-serif; }
.container { margin-top: 50px; max-width: 800px; }
.card { border: none; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
.card-header { background: #198754; color: white; }
</style>
</head>
<body>
<div class="container">
  <div class="card">
    <div class="card-header"><h4>🔔 Notifications</h4></div>
    <div class="card-body">
      <?php if ($result->num_rows > 0): ?>
        <ul class="list-group">
          <?php while($n = $result->fetch_assoc()): ?>
            <li class="list-group-item">
              <?= htmlspecialchars($n['message']) ?><br>
              <small class="text-muted"><?= $n['created_at'] ?></small>
            </li>
          <?php endwhile; ?>
        </ul>
      <?php else: ?>
        <p class="text-muted text-center">No notifications yet.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
