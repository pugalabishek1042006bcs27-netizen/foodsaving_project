<?php
session_start();
include 'db.php';

if (!isset($_SESSION['donor_id'])) {
    header("Location: donor-login.php");
    exit();
}

$donor_id = $_SESSION['donor_id'];

// Fetch donor info (name + profile picture)
$stmt = $conn->prepare("SELECT name, profile_picture FROM donor WHERE donor_id = ?");
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$stmt->bind_result($donor_name, $profile_picture);
$stmt->fetch();
$stmt->close();

// Default profile picture
if (empty($profile_picture) || !file_exists($profile_picture)) {
    $profile_picture = "uploads/default-avatar.png";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Donor Dashboard | FoodSaver</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    :root {
      --primary:#2ecc71;
      --secondary:#27ae60;
      --light-bg:#f4f9f4;
      --white:#fff;
      --text:#333;
      --shadow:0 4px 25px rgba(0,0,0,0.1);
    }
    * {margin:0; padding:0; box-sizing:border-box; font-family:"Poppins", sans-serif;}
    body {background:linear-gradient(135deg,#e9f7ef,#f8fdf8); color:var(--text);}
    header {
      background:linear-gradient(135deg,var(--primary),var(--secondary));
      color:white;
      padding:1rem 3rem;
      display:flex;
      justify-content:space-between;
      align-items:center;
      box-shadow:var(--shadow);
      position:sticky;top:0;z-index:99;
    }
    header .logo {font-size:1.8rem;font-weight:600;display:flex;align-items:center;gap:.5rem;}
    header ul {list-style:none;display:flex;gap:2rem;}
    header ul a {color:white;text-decoration:none;font-weight:500;transition:.3s;}
    header ul a:hover {color:#d1ffd7;}
    .hero {
      max-width:1000px;margin:2rem auto;background:var(--white);border-radius:18px;
      padding:2rem;text-align:center;box-shadow:var(--shadow);
      display:flex;flex-direction:column;align-items:center;gap:1rem;
    }
    .hero img {
      width:120px;height:120px;border-radius:50%;object-fit:cover;
      box-shadow:0 0 10px rgba(0,0,0,0.1);border:4px solid var(--primary);
    }
    .hero h1 {color:var(--secondary);margin-top:.5rem;}
    .hero p {color:#555;margin-top:.2rem;}
    .actions {
      display:flex;justify-content:center;gap:1rem;margin-top:1.5rem;flex-wrap:wrap;
    }
    .btn {
      background:linear-gradient(135deg,var(--primary),var(--secondary));
      color:white;border:none;padding:0.9rem 1.8rem;border-radius:10px;
      cursor:pointer;text-decoration:none;font-weight:600;display:flex;align-items:center;
      gap:.5rem;box-shadow:0 5px 15px rgba(39,174,96,0.3);transition:.3s;
    }
    .btn:hover {transform:translateY(-3px);box-shadow:0 7px 20px rgba(39,174,96,0.4);}
    .btn.secondary {background:linear-gradient(135deg,#6c757d,#5a6268);}
    .table-section {
      max-width:1100px;margin:2rem auto;background:rgba(255,255,255,0.9);
      backdrop-filter:blur(8px);border-radius:16px;box-shadow:var(--shadow);
      padding:1.5rem;display:none;animation:fadeIn .5s ease-in-out;
    }
    @keyframes fadeIn {from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
    table {width:100%;border-collapse:collapse;margin-top:1rem;}
    th,td {padding:1rem;border-bottom:1px solid #e9ecef;text-align:left;}
    th {background:#f8f9fa;}
    tr:hover {background:#f1fdf3;}
    .status-badge {padding:.4rem .8rem;border-radius:20px;font-size:.85rem;font-weight:600;text-transform:uppercase;}
    .status-pending {background:#fff3cd;color:#856404;}
    .status-accepted {background:#d1ecf1;color:#0c5460;}
    .status-in_progress {background:#cce5ff;color:#004085;}
    .status-completed {background:#d4edda;color:#155724;}
    .status-cancelled {background:#f8d7da;color:#721c24;}
    footer {
      text-align:center;padding:1rem;background:linear-gradient(135deg,#4CAF50,#45a049);
      color:white;font-size:0.9rem;margin-top:2rem;border-top-left-radius:12px;border-top-right-radius:12px;
    }
</style>
</head>
<body>

<header>
  <div class="logo"><i class="fas fa-leaf"></i> FoodSaver</div>
  <ul>
    <li><a href="donor-dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="donor-donation.php"><i class="fas fa-hand-holding-heart"></i> Donate</a></li>
    <li><a href="update-donor-profile.php"><i class="fas fa-user"></i> Profile</a></li>
    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</header>

<section class="hero">
  <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
  <h1>Welcome, <?php echo htmlspecialchars($donor_name); ?> 🌿</h1>
  <p>Thank you for making a difference. Manage your donations and track their journey below.</p>
  <div class="actions">
    <a href="donor-donation.php" class="btn"><i class="fas fa-upload"></i> Upload New Donation</a>
    <button class="btn secondary" id="viewHistory"><i class="fas fa-clock-rotate-left"></i> View Donation History</button>
  </div>
</section>

<section class="table-section" id="donationSection">
  <h2 style="text-align:center;color:var(--secondary);margin-bottom:1rem;"><i class="fas fa-box"></i> Your Donations</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th><th>Food Type</th><th>Quantity</th><th>OTP Code</th><th>Status</th><th>Expiry Date</th><th>Uploaded</th>
      </tr>
    </thead>
    <tbody id="donationTableBody">
      <tr><td colspan="7" style="text-align:center;color:#666;">Click "View Donation History" to load your data.</td></tr>
    </tbody>
  </table>
</section>

<footer>
  &copy; <?php echo date("Y"); ?> FoodSaver — Built with ❤️ for communities.
</footer>

<script>
document.getElementById("viewHistory").addEventListener("click", () => {
  const section = document.getElementById("donationSection");
  section.style.display = "block";

  fetch("get_donor_donations.php")
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById("donationTableBody");
      tbody.innerHTML = "";
      if (!data || data.length === 0) {
        tbody.innerHTML = `<tr><td colspan='7' style='text-align:center;'>No donations found.</td></tr>`;
        return;
      }
      data.forEach(row => {
        let statusClass = row.status ? row.status.toLowerCase().replace(" ", "_") : "pending";
        tbody.innerHTML += `
          <tr>
            <td>${row.donation_id}</td>
            <td>${row.food_type}</td>
            <td>${row.quantity}</td>
            <td><b>${row.otp_code ? row.otp_code : '—'}</b></td>
            <td><span class="status-badge status-${statusClass}">${row.status ? row.status.toUpperCase() : 'PENDING'}</span></td>
            <td>${row.expiry_date}</td>
            <td>${row.upload_date}</td>
          </tr>`;
      });
    })
    .catch(err => alert("Error loading donations: " + err));
});
</script>
</body>
</html>
