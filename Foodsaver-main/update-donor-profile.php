<?php
session_start();
include 'db.php';

if (!isset($_SESSION['donor_id'])) {
    header("Location: donor-login.php");
    exit();
}

$donor_id = $_SESSION['donor_id'];

// Handle POST update request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];
    $new_password = $_POST['new_password'] ?? '';
    $profile_picture = '';

    // Handle image upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $filename = "donor_" . $donor_id . "_" . basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . $filename;
        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
        $profile_picture = $target_file;
    }

    // Build update query dynamically
    $query = "UPDATE donor SET name=?, email=?, contact_number=?, address=?";
    $params = [$name, $email, $contact_number, $address];
    $types = "ssss";

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $query .= ", password=?";
        $params[] = $hashed_password;
        $types .= "s";
    }
    if (!empty($profile_picture)) {
        $query .= ", profile_picture=?";
        $params[] = $profile_picture;
        $types .= "s";
    }

    $query .= " WHERE donor_id=?";
    $params[] = $donor_id;
    $types .= "i";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Profile updated successfully!'); window.location.href='donor-dashboard.php';</script>";
    exit();
}

// If GET: Load donor data
$stmt = $conn->prepare("SELECT name, email, contact_number, address, profile_picture FROM donor WHERE donor_id=?");
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$stmt->bind_result($name, $email, $contact_number, $address, $profile_picture);
$stmt->fetch();
$stmt->close();

if (empty($profile_picture)) {
    $profile_picture = "uploads/default-avatar.png";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Donor Profile | FoodSaver</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body {
  font-family: "Poppins", sans-serif;
  background: linear-gradient(135deg, #e9f7ef, #f8fdf8);
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}
form {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 4px 25px rgba(0,0,0,0.1);
  width: 420px;
}
h2 {
  text-align: center;
  color: #27ae60;
  margin-bottom: 1.5rem;
}
label {display:block;margin-top:1rem;font-weight:600;}
input, textarea {
  width:100%;padding:.7rem;margin-top:.3rem;border:1px solid #ccc;border-radius:8px;
}
button {
  background:linear-gradient(135deg,#2ecc71,#27ae60);
  color:white;border:none;padding:.8rem 1.5rem;border-radius:8px;
  cursor:pointer;width:100%;font-weight:600;margin-top:1.2rem;
}
button:hover {opacity:0.9;}
.profile-pic {
  display:flex;justify-content:center;margin-bottom:1rem;
}
.profile-pic img {
  width:120px;height:120px;border-radius:50%;object-fit:cover;border:4px solid #27ae60;
}
</style>
</head>
<body>
<form method="POST" enctype="multipart/form-data">
  <h2><i class="fas fa-user-edit"></i> Update Profile</h2>
  <div class="profile-pic">
    <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
  </div>

  <label>Name</label>
  <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

  <label>Email</label>
  <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

  <label>Contact Number</label>
  <input type="text" name="contact_number" value="<?php echo htmlspecialchars($contact_number); ?>" required>

  <label>Address</label>
  <textarea name="address" rows="3" required><?php echo htmlspecialchars($address); ?></textarea>

  <label>Profile Picture</label>
  <input type="file" name="profile_picture" accept="image/*">

  <label>Change Password (optional)</label>
  <input type="password" name="new_password" placeholder="Enter new password">

  <button type="submit">Save Changes</button>
</form>
</body>
</html>
