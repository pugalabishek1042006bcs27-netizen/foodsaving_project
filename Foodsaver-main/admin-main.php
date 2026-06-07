<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
  header("Location: admin-login.html");
  exit();
}

$admin_name = $_SESSION['admin_name'];

// --- Handle actions ---
if (isset($_POST['approve_cert'])) {
  $id = $_POST['cert_id'];
  $conn->query("UPDATE certificates SET status='Approved' WHERE cert_id=$id");
}
if (isset($_POST['reject_cert'])) {
  $id = $_POST['cert_id'];
  $conn->query("UPDATE certificates SET status='Rejected' WHERE cert_id=$id");
}
if (isset($_POST['delete_user'])) {
  $table = $_POST['table'];
  $id = $_POST['id'];
  $idField = ($table === 'receivers') ? 'receiver_id' : "{$table}_id";

  // If deleting a donor, delete related donations first
  if ($table === 'donor') {
      $conn->query("DELETE FROM food_donations WHERE donor_id=$id");
  }

  // If deleting a receiver, delete their certificates too
  if ($table === 'receivers') {
      $conn->query("DELETE FROM certificates WHERE receiver_id=$id");
  }

  $conn->query("DELETE FROM $table WHERE $idField=$id");
}


// --- Statistics ---
$donors = $conn->query("SELECT COUNT(*) AS c FROM donor")->fetch_assoc()['c'];
$volunteers = $conn->query("SELECT COUNT(*) AS c FROM volunteer")->fetch_assoc()['c'];
$receivers = $conn->query("SELECT COUNT(*) AS c FROM receivers")->fetch_assoc()['c'];
$donations = $conn->query("SELECT COUNT(*) AS c FROM food_donations")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FoodSaver | Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet">
<style>
:root {
  --green: #27ae60;
  --light-green: #a8e6a1;
  --background: #f8fdf9;
  --card-bg: #ffffff;
  --border: #d6e9d9;
  --text-dark: #2c3e50;
  --accent: #4CAF50;
}
body {
  margin: 0;
  font-family: "Poppins", sans-serif;
  display: flex;
  background: var(--background);
  color: var(--text-dark);
}

/* Sidebar */
.sidebar {
  width: 240px;
  background: linear-gradient(180deg, var(--green), #1b8f4a);
  color: white;
  padding-top: 25px;
  height: 100vh;
  position: fixed;
  box-shadow: 3px 0 10px rgba(0,0,0,0.1);
}
.sidebar h2 {
  text-align: center;
  margin-bottom: 30px;
  font-weight: 600;
}
.sidebar a {
  display: flex;
  align-items: center;
  color: white;
  text-decoration: none;
  padding: 12px 25px;
  font-weight: 500;
  transition: 0.3s;
}
.sidebar a:hover, .sidebar a.active {
  background: rgba(255, 255, 255, 0.2);
  border-left: 4px solid #fff;
}
.sidebar i {
  margin-right: 10px;
  font-size: 20px;
}
.logout {
  background: #e74c3c;
  margin: 20px;
  padding: 10px;
  border-radius: 8px;
  text-align: center;
  display: block;
  transition: 0.3s;
}
.logout:hover { background: #c0392b; }

/* Main */
.main {
  margin-left: 240px;
  padding: 30px;
  width: calc(100% - 240px);
}
header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--card-bg);
  border-radius: 10px;
  padding: 15px 25px;
  margin-bottom: 25px;
  border: 1px solid var(--border);
}
header h1 {
  font-size: 1.5em;
  color: var(--green);
}
header span {
  font-weight: 500;
  color: #555;
}

/* Stats Cards */
.stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}
.card {
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 20px;
  text-align: center;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  transition: transform 0.3s ease;
}
.card:hover { transform: translateY(-5px); }
.card i { font-size: 35px; color: var(--green); }
.card h3 { margin: 10px 0; }
.card p { font-size: 22px; font-weight: bold; color: var(--text-dark); }

/* Containers */
.container {
  background: var(--card-bg);
  margin-top: 40px;
  border-radius: 10px;
  padding: 25px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  border: 1px solid var(--border);
}
.container h3 {
  border-left: 5px solid var(--green);
  padding-left: 12px;
  color: var(--green);
  font-weight: 600;
}

/* Table */
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}
th, td {
  padding: 10px;
  text-align: center;
  border-bottom: 1px solid var(--border);
}
th {
  background: var(--light-green);
  color: #2c3e50;
}
tr:hover { background: #f0fff0; }
button {
  background: var(--green);
  border: none;
  color: white;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
}
button:hover { background: var(--accent); }
button.reject { background: #e74c3c; }
button.reject:hover { background: #c0392b; }

/* Search */
.search-bar {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 10px;
}
.search-bar input {
  padding: 8px 10px;
  border: 1px solid var(--border);
  border-radius: 6px;
  width: 220px;
  outline: none;
}

/* Responsive */
@media (max-width: 768px) {
  .sidebar { width: 80px; }
  .main { margin-left: 80px; }
  .sidebar a span, .sidebar h2 { display: none; }
}
</style>
</head>

<body>
  <div class="sidebar">
    <h2>🍃 FoodSaver</h2>
    <a href="#overview" class="active"><i class="ri-dashboard-line"></i><span>Dashboard</span></a>
    <a href="#users"><i class="ri-user-3-line"></i><span>Users</span></a>
    <a href="#donations"><i class="ri-hand-heart-line"></i><span>Donations</span></a>
    <a href="#certificates"><i class="ri-file-check-line"></i><span>Certificates</span></a>
    <a href="#reports"><i class="ri-bar-chart-line"></i><span>Reports</span></a>
    <a href="logout.php" class="logout"><i class="ri-logout-box-line"></i> Logout</a>
  </div>

  <div class="main">
    <header>
      <h1>Welcome, <?= htmlspecialchars($admin_name) ?> 👋</h1>
      <span><i class="ri-shield-user-line"></i> Admin Dashboard</span>
    </header>

    <!-- Stats Section -->
    <section class="stats" id="overview">
      <div class="card"><i class="ri-store-line"></i><h3>Donors</h3><p><?= $donors ?></p></div>
      <div class="card"><i class="ri-truck-line"></i><h3>Volunteers</h3><p><?= $volunteers ?></p></div>
      <div class="card"><i class="ri-community-line"></i><h3>Receivers</h3><p><?= $receivers ?></p></div>
      <div class="card"><i class="ri-hand-heart-line"></i><h3>Donations</h3><p><?= $donations ?></p></div>
    </section>

    <!-- Manage Users -->
    <div class="container" id="users">
      <h3>Manage Users</h3>
      <div class="search-bar"><input type="text" id="userSearch" placeholder="Search users..."></div>
      <table id="userTable">
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Type</th><th>Action</th></tr>
        <?php
        $tables = ['donor','volunteer','receivers'];
        foreach ($tables as $t) {
          $res = $conn->query("SELECT * FROM $t");
          while ($r = $res->fetch_assoc()) {
            // ✅ Correct ID field for receivers
            $idField = ($t === 'receivers') ? 'receiver_id' : "{$t}_id";
            $nameField = isset($r['name']) ? 'name' : (isset($r['receiver_name']) ? 'receiver_name' : 'org_name');
            $displayName = htmlspecialchars($r[$nameField]);
            echo "<tr>
              <td>{$r[$idField]}</td>
              <td>$displayName</td>
              <td>{$r['email']}</td>
              <td>".ucfirst($t)."</td>
              <td>
                <form method='POST'>
                  <input type='hidden' name='id' value='{$r[$idField]}'>
                  <input type='hidden' name='table' value='$t'>
                  <button name='delete_user' class='reject'>Delete</button>
                </form>
              </td>
            </tr>";
          }
        }
        ?>
      </table>
    </div>

    <!-- Donations -->
    <div class="container" id="donations">
      <h3>Food Donations</h3>
      <table>
        <tr><th>ID</th><th>Donor ID</th><th>Food Type</th><th>Quantity</th><th>Status</th><th>Action</th></tr>
        <?php
        $res = $conn->query("SELECT * FROM food_donations");
        if ($res->num_rows > 0) {
          while ($row = $res->fetch_assoc()) {
            echo "<tr>
              <td>{$row['donation_id']}</td>
              <td>{$row['donor_id']}</td>
              <td>{$row['food_type']}</td>
              <td>{$row['quantity']}</td>
              <td>{$row['status']}</td>
              <td>
                <form method='POST'>
                  <input type='hidden' name='id' value='{$row['donation_id']}'>
                  <button name='delete_donation' class='reject'>Delete</button>
                </form>
              </td>
            </tr>";
          }
        } else echo "<tr><td colspan='6'>No donations yet.</td></tr>";
        ?>
      </table>
    </div>

    <!-- Certificates -->
    <div class="container" id="certificates">
      <h3>Receiver Certificates</h3>
      <table>
        <tr><th>ID</th><th>Receiver ID</th><th>File</th><th>Status</th><th>Action</th></tr>
        <?php
        $res = $conn->query("SELECT * FROM certificates ORDER BY uploaded_at DESC");
        if ($res && $res->num_rows > 0) {
          while ($row = $res->fetch_assoc()) {
            echo "<tr>
              <td>{$row['cert_id']}</td>
              <td>{$row['receiver_id']}</td>
              <td><a href='uploads/certificates/{$row['file_path']}' target='_blank' style='color:#2c7c4d;'>View</a></td>
              <td>{$row['status']}</td>
              <td>
                <form method='POST'>
                  <input type='hidden' name='cert_id' value='{$row['cert_id']}'>
                  <button name='approve_cert'>Approve</button>
                  <button name='reject_cert' class='reject'>Reject</button>
                </form>
              </td>
            </tr>";
          }
        } else echo "<tr><td colspan='5'>No certificates uploaded yet.</td></tr>";
        ?>
      </table>
    </div>

    <!-- Reports -->
    <div class="container" id="reports">
      <h3>System Reports</h3>
      <ul style="list-style:none; padding:0; color:#2c3e50;">
        <li>📦 Total Donations: <?= $donations ?></li>
        <li>👨‍🍳 Active Donors: <?= $donors ?></li>
        <li>🚚 Active Volunteers: <?= $volunteers ?></li>
        <li>🏠 Registered Receivers: <?= $receivers ?></li>
      </ul>
    </div>
  </div>

<script>
document.getElementById("userSearch").addEventListener("keyup", function() {
  const filter = this.value.toLowerCase();
  const rows = document.querySelectorAll("#userTable tr:not(:first-child)");
  rows.forEach(r => {
    const text = r.innerText.toLowerCase();
    r.style.display = text.includes(filter) ? "" : "none";
  });
});
</script>
</body>
</html>
