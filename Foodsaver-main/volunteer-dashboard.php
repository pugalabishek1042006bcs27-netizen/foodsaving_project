<?php
session_start();
include 'db.php';

// Check login
if (!isset($_SESSION['volunteer_id'])) {
    header("Location: volunteer-login.html");
    exit();
}
$volunteer_id = $_SESSION['volunteer_id'];

// Fetch volunteer details
$stmt = $conn->prepare("SELECT volunteer_id, name, email, password, contact_number, region, availability, profile_picture FROM volunteer WHERE volunteer_id = ?");
$stmt->bind_param("i", $volunteer_id);
$stmt->execute();
$result = $stmt->get_result();
$volunteer = $result->fetch_assoc();
$stmt->close();

// Stats
$order_query = $conn->prepare("
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed,
        SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS in_progress
    FROM food_requests
    WHERE volunteer_id = ?
");
$order_query->bind_param("i", $volunteer_id);
$order_query->execute();
$order_result = $order_query->get_result()->fetch_assoc() ?: [];
$order_query->close();

$total_orders = $order_result['total'] ?? 0;
$completed_orders = $order_result['completed'] ?? 0;
$in_progress_orders = $order_result['in_progress'] ?? 0;

// Recent 5 requests
$recent_query = $conn->prepare("
    SELECT request_date, details, status
    FROM food_requests
    WHERE volunteer_id = ?
    ORDER BY request_date DESC
    LIMIT 5
");
$recent_query->bind_param("i", $volunteer_id);
$recent_query->execute();
$recent_result = $recent_query->get_result();
$recent_rows = $recent_result->fetch_all(MYSQLI_ASSOC);
$recent_query->close();

// Default image if none uploaded
$profile_img = !empty($volunteer['profile_picture']) && file_exists($volunteer['profile_picture'])
    ? $volunteer['profile_picture']
    : 'uploads/default-avatar.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Volunteer Dashboard - FoodSaver</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{
  --primary:#2e8b57;
  --accent:#27ae60;
  --muted:#6b6b6b;
  --card:#ffffff;
}
*{box-sizing:border-box;font-family:'Poppins',sans-serif;margin:0;padding:0}
body{background:linear-gradient(180deg,#f0fbf5,#f6fcff);color:#222;overflow-x:hidden}
body.modal-open{overflow:hidden}

nav{
  background:var(--primary);color:#fff;padding:18px 36px;
  display:flex;justify-content:space-between;align-items:center;
  box-shadow:0 6px 20px rgba(30,90,50,0.12);position:sticky;top:0;z-index:20;
}
nav h2{font-size:1.4rem;font-weight:600;display:flex;align-items:center;gap:.6rem}
nav a{color:#fff;text-decoration:none;margin-left:18px;font-weight:500;opacity:.95}
nav a:hover{opacity:1}

.container{max-width:1150px;margin:36px auto;padding:0 20px}
.card{background:var(--card);border-radius:18px;padding:30px;box-shadow:0 10px 40px rgba(20,60,30,0.06);}
.profile-row{display:flex;gap:28px;align-items:center}
.avatar{
  width:120px;height:120px;border-radius:50%;object-fit:cover;
  border:4px solid var(--primary);background:#fff;
  box-shadow:0 6px 20px rgba(46,139,87,0.08);
}
.profile-info h3{font-size:1.4rem;color:var(--primary);margin-bottom:8px}
.profile-info p{color:var(--muted);margin:6px 0}
.btn{display:inline-flex;align-items:center;gap:.6rem;padding:10px 18px;border-radius:28px;
  background:linear-gradient(135deg,var(--primary),var(--accent));color:white;text-decoration:none;
  border:none;cursor:pointer;font-weight:600;box-shadow:0 6px 18px rgba(46,139,87,0.18);}
.btn.secondary{background:transparent;color:var(--primary);border:1px solid rgba(46,139,87,0.25);box-shadow:none}
.stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:28px}
.stat{background:#f6fff6;border-radius:14px;padding:26px;text-align:center;border:1px solid #e6f4ea}
.stat h2{font-size:36px;color:var(--primary);margin-bottom:6px}
.manage-btn{display:block;margin:30px auto 0;width:240px;text-align:center}

/* Recent deliveries */
.recent{margin-top:34px}
table{width:100%;border-collapse:collapse;margin-top:12px;border-radius:8px;overflow:hidden}
th,td{padding:12px 14px;text-align:left}
th{background:var(--primary);color:#fff;font-weight:600;text-transform:uppercase;font-size:12px}
tr:nth-child(even){background:#fbfff9}
tr:hover{background:#f1fff3}
td.status{text-align:center;font-weight:700}
.status.Pending{color:#ffb300}
.status.Approved{color:#007bff}
.status.Completed{color:#2e8b57}
.status.Rejected{color:#d9534f}

/* Modal */
.modal-backdrop{
  position:fixed;inset:0;background:rgba(0,0,0,0.4);
  display:none;align-items:center;justify-content:center;z-index:999;
}
.modal{
  width:90%;max-width:460px;background:var(--card);border-radius:15px;padding:22px 24px;
  box-shadow:0 12px 40px rgba(0,0,0,0.2);max-height:90vh;overflow-y:auto;animation:fadeIn .25s ease;
}
@keyframes fadeIn{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}
.modal header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.modal label{display:block;margin:8px 0 6px;font-weight:600;color:#333}
.modal input{
  width:100%;padding:10px 12px;border-radius:8px;border:1px solid #ccc;font-size:14px;
}
.modal input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 4px var(--accent);}
.modal .actions{display:flex;gap:10px;justify-content:flex-end;margin-top:16px}
.small{font-size:13px;color:#666}
#profilePreview{
  width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid var(--primary);
  display:block;margin:10px auto;
}
.change-photo-btn{
  display:block;margin:10px auto;background:transparent;color:var(--primary);
  font-weight:600;border:none;cursor:pointer;
}
@media(max-width:880px){.profile-row{flex-direction:column;text-align:center}.stats-grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<nav>
  <h2><i class="fas fa-hand-holding-heart"></i> FoodSaver Volunteer Panel</h2>
  <div>
    <a href="volunteer-dashboard.php">Dashboard</a>
    <a href="volunteer-activity.php">Activities</a>
    <a href="logout.php">Logout</a>
  </div>
</nav>

<div class="container">
  <div class="card">
    <div class="profile-row">
      <div style="text-align:center">
        <img src="<?php echo htmlspecialchars($profile_img); ?>" alt="Avatar" id="profileImage" class="avatar">
        <div style="margin-top:12px">
          <button class="btn secondary" id="editProfileBtn"><i class="fas fa-user-edit"></i> Edit Profile</button>
        </div>
      </div>
      <div class="profile-info" style="flex:1">
        <h3 id="displayName"><?php echo htmlspecialchars($volunteer['name']); ?></h3>
        <p class="small"><i class="fas fa-envelope"></i> <span id="displayEmail"><?php echo htmlspecialchars($volunteer['email']); ?></span></p>
        <p class="small"><i class="fas fa-map-marker-alt"></i> <span id="displayRegion"><?php echo htmlspecialchars($volunteer['region']); ?></span></p>
        <p class="small"><i class="fas fa-phone"></i> <span id="displayContact"><?php echo htmlspecialchars($volunteer['contact_number']); ?></span></p>
        <p class="small"><i class="fas fa-clock"></i> <span id="displayAvailability"><?php echo htmlspecialchars($volunteer['availability'] ?? 'Not set'); ?></span></p>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat"><h2><?php echo (int)$total_orders; ?></h2><div class="small">Total Orders</div></div>
      <div class="stat"><h2><?php echo (int)$completed_orders; ?></h2><div class="small">Completed</div></div>
      <div class="stat"><h2><?php echo (int)$in_progress_orders; ?></h2><div class="small">In Progress</div></div>
    </div>

    <a class="btn manage-btn" href="volunteer-activity.php"><i class="fas fa-truck"></i> Manage Deliveries</a>

    <div class="recent">
      <h4 style="color:var(--primary);margin-bottom:8px">📦 Recent Delivery Requests</h4>
      <table>
        <thead><tr><th>#</th><th>Request Date</th><th>Details</th><th style="text-align:center">Status</th></tr></thead>
        <tbody>
        <?php if(count($recent_rows) > 0): $i=1; foreach($recent_rows as $r): ?>
          <tr>
            <td><?php echo $i++; ?></td>
            <td><?php echo date("d M Y, h:i A", strtotime($r['request_date'])); ?></td>
            <td><?php echo htmlspecialchars(strlen($r['details']) > 80 ? substr($r['details'],0,80).'...' : $r['details']); ?></td>
            <td class="status <?php echo htmlspecialchars($r['status']); ?>"><?php echo htmlspecialchars($r['status']); ?></td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="4" style="text-align:center;color:#888">No recent deliveries</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ======== MODAL ========= -->
<div class="modal-backdrop" id="modal">
  <div class="modal" role="dialog">
    <header>
      <h3>Edit Profile</h3>
      <button id="closeModal" class="btn secondary"><i class="fas fa-times"></i></button>
    </header>
    <form id="editProfileForm" enctype="multipart/form-data">
      <img src="<?php echo htmlspecialchars($profile_img); ?>" id="profilePreview" alt="Preview">
      <input type="file" id="profile_picture" name="profile_picture" accept="image/*" style="display:none;">
      <button type="button" class="change-photo-btn" id="changePhotoBtn"><i class="fas fa-camera"></i> Change Photo</button>

      <label>Name</label>
      <input name="name" value="<?php echo htmlspecialchars($volunteer['name']); ?>" required>

      <label>Email</label>
      <input type="email" name="email" value="<?php echo htmlspecialchars($volunteer['email']); ?>" required>

      <label>Region</label>
      <input name="region" value="<?php echo htmlspecialchars($volunteer['region']); ?>">

      <label>Contact Number</label>
      <input name="contact_number" value="<?php echo htmlspecialchars($volunteer['contact_number']); ?>">

      <label>Availability</label>
      <input name="availability" value="<?php echo htmlspecialchars($volunteer['availability']); ?>">

      <hr style="margin:15px 0;border:none;border-top:1px solid #eee">
      <p class="small">Enter your <b>current password</b> to save. Enter a <b>new password</b> to change it.</p>

      <label>Current Password (required)</label>
      <input type="password" name="current_password" required>

      <label>New Password (optional)</label>
      <input type="password" name="new_password" placeholder="Leave blank to keep current">

      <div class="actions">
        <button type="button" id="cancelBtn" class="btn secondary">Cancel</button>
        <button type="submit" class="btn"><i class="fas fa-save"></i> Save</button>
      </div>
      <div id="formMessage" style="margin-top:10px;font-size:14px;"></div>
    </form>
  </div>
</div>

<script>
const modal = document.getElementById('modal');
const editBtn = document.getElementById('editProfileBtn');
const closeBtn = document.getElementById('closeModal');
const cancelBtn = document.getElementById('cancelBtn');
const changePhotoBtn = document.getElementById('changePhotoBtn');
const photoInput = document.getElementById('profile_picture');
const preview = document.getElementById('profilePreview');
const displayPhoto = document.getElementById('profileImage');

function openModal(){ modal.style.display='flex'; document.body.classList.add('modal-open'); }
function closeModalFn(){ modal.style.display='none'; document.body.classList.remove('modal-open'); }

editBtn.onclick = openModal;
closeBtn.onclick = closeModalFn;
cancelBtn.onclick = closeModalFn;
window.onclick = e => { if(e.target===modal) closeModalFn(); };

// Photo preview logic
changePhotoBtn.onclick = () => photoInput.click();
photoInput.onchange = e => {
  const file = e.target.files[0];
  if(file){
    const reader = new FileReader();
    reader.onload = ev => preview.src = ev.target.result;
    reader.readAsDataURL(file);
  }
};

const form=document.getElementById('editProfileForm');
const msg=document.getElementById('formMessage');

form.addEventListener('submit',async(e)=>{
  e.preventDefault();
  msg.textContent='';
  const btn=form.querySelector('button[type="submit"]');
  btn.disabled=true;btn.style.opacity='.6';
  try{
    const res=await fetch('update-volunteer-profile.php',{method:'POST',body:new FormData(form)});
    const data=await res.json();
    if(data.success){
      msg.style.color='green';msg.textContent='Profile updated successfully!';
      document.getElementById('displayName').textContent=data.updated.name;
      document.getElementById('displayEmail').textContent=data.updated.email;
      document.getElementById('displayRegion').textContent=data.updated.region;
      document.getElementById('displayContact').textContent=data.updated.contact_number;
      document.getElementById('displayAvailability').textContent=data.updated.availability||'Not set';
      if(data.updated.profile_picture){
        displayPhoto.src = data.updated.profile_picture;
        preview.src = data.updated.profile_picture;
      }
      setTimeout(()=>closeModalFn(),1000);
    } else {
      msg.style.color='crimson';msg.textContent=data.message||'Update failed.';
    }
  }catch(err){
    msg.style.color='crimson';msg.textContent='Error: '+err.message;
  }finally{
    btn.disabled=false;btn.style.opacity='1';
  }
});
</script>
</body>
</html>
