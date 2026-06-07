<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['receiver_id'])) {
    header("Location: receiver-login.php");
    exit();
}

$receiver_id = $_SESSION['receiver_id'];

// Fetch current info
$stmt = $conn->prepare("SELECT receiver_name, email, address, city, state, pincode, profile_picture FROM receivers WHERE receiver_id = ?");
$stmt->bind_param("i", $receiver_id);
$stmt->execute();
$result = $stmt->get_result();
$receiver = $result->fetch_assoc();
$stmt->close();

$msg = "";

// Handle update form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_name = $_POST['receiver_name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $pincode = $_POST['pincode'];

    // Handle optional profile picture update
    $profile_picture = $receiver['profile_picture'];
    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/profile/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $file_name = basename($_FILES['profile_picture']['name']);
        $target_file = $target_dir . $file_name;
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file);
        $profile_picture = $file_name;
    }

    // Update database
    $stmt = $conn->prepare("UPDATE receivers SET receiver_name=?, email=?, address=?, city=?, state=?, pincode=?, profile_picture=? WHERE receiver_id=?");
    $stmt->bind_param("sssssssi", $receiver_name, $email, $address, $city, $state, $pincode, $profile_picture, $receiver_id);
    if ($stmt->execute()) {
        $_SESSION['receiver_name'] = $receiver_name; // update session value
        $msg = "Profile updated successfully!";
    } else {
        $msg = "Error updating profile: " . $conn->error;
    }
    $stmt->close();

    // Re-fetch updated info
    $stmt = $conn->prepare("SELECT receiver_name, email, address, city, state, pincode, profile_picture FROM receivers WHERE receiver_id = ?");
    $stmt->bind_param("i", $receiver_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $receiver = $result->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Update Receiver Profile | FoodSaver</title>
<style>
body {
    font-family: "Poppins", sans-serif;
    background: #f6f9f6;
    margin: 0;
}
.container {
    max-width: 600px;
    margin: 50px auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.1);
}
h2 {
    color: #2b8a3e;
    text-align: center;
}
input, button {
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
.message {
    background: #e8ffe8;
    padding: 10px;
    border-radius: 6px;
    color: #2b8a3e;
    margin-bottom: 15px;
    text-align: center;
}
.back-btn {
    display: block;
    text-align: center;
    margin-top: 15px;
    text-decoration: none;
    color: #2b8a3e;
    font-weight: bold;
}
</style>
</head>
<body>
<div class="container">
    <h2>Edit Receiver Profile</h2>

    <?php if ($msg) echo "<div class='message'>$msg</div>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Name:</label>
        <input type="text" name="receiver_name" value="<?= htmlspecialchars($receiver['receiver_name']) ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($receiver['email']) ?>" required>

        <label>Address:</label>
        <input type="text" name="address" value="<?= htmlspecialchars($receiver['address']) ?>" required>

        <label>City:</label>
        <input type="text" name="city" value="<?= htmlspecialchars($receiver['city']) ?>" required>

        <label>State:</label>
        <input type="text" name="state" value="<?= htmlspecialchars($receiver['state']) ?>" required>

        <label>Pincode:</label>
        <input type="text" name="pincode" value="<?= htmlspecialchars($receiver['pincode']) ?>" required>

        <label>Profile Picture:</label>
        <input type="file" name="profile_picture">

        <button type="submit">Update Profile</button>
    </form>

    <a href="receiver-dashboard.php" class="back-btn">← Back to Dashboard</a>
</div>
</body>
</html>
