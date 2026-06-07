<?php
// update-volunteer-profile.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['volunteer_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

include 'db.php';

$id = (int) $_SESSION['volunteer_id'];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$region = trim($_POST['region'] ?? '');
$contact = trim($_POST['contact_number'] ?? '');
$availability = trim($_POST['availability'] ?? '');
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

if ($name === '' || $email === '' || $current_password === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Name, email and current password are required']);
    exit;
}

// Fetch stored password
$stmt = $conn->prepare("SELECT password, profile_picture FROM volunteer WHERE volunteer_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($stored_password, $existing_picture);
$got = $stmt->fetch();
$stmt->close();

if (!$got) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Volunteer not found']);
    exit;
}

// normalize
$stored_password = (string)$stored_password;
$current_password = (string)$current_password;

// Detect if stored value is a hash
function looks_like_password_hash($s) {
    return (bool) preg_match('/^\$(2y|2b|2a)\$|^\$argon/', $s);
}

$is_hashed = looks_like_password_hash($stored_password);
$verified = false;
$upgrade_plain = false;

// Verify current password
if ($is_hashed) {
    if (password_verify($current_password, $stored_password)) {
        $verified = true;
    }
} else {
    if (trim($current_password) === trim($stored_password)) {
        $verified = true;
        $upgrade_plain = true;
    }
}

if (!$verified) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Incorrect current password']);
    exit;
}

// Handle password update
if (!empty($new_password)) {
    $password_to_store = password_hash($new_password, PASSWORD_DEFAULT);
} else {
    if ($upgrade_plain) {
        $password_to_store = password_hash($current_password, PASSWORD_DEFAULT);
    } else {
        $password_to_store = $stored_password;
    }
}

// Handle profile picture upload (optional)
$profile_picture_path = $existing_picture; // default to old picture

if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
    $fileName = $_FILES['profile_picture']['name'];
    $fileSize = $_FILES['profile_picture']['size'];
    $fileType = $_FILES['profile_picture']['type'];
    $fileNameCmps = pathinfo($fileName);
    $fileExtension = strtolower($fileNameCmps['extension']);

    $allowedExtensions = ['jpg', 'jpeg', 'png'];

    if (in_array($fileExtension, $allowedExtensions)) {
        $uploadFolder = 'uploads/volunteer_profiles/';
        if (!is_dir($uploadFolder)) {
            mkdir($uploadFolder, 0777, true);
        }

        // Create a safe unique filename
        $newFileName = 'volunteer_' . $id . '_' . time() . '.' . $fileExtension;
        $dest_path = $uploadFolder . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // Optionally delete old picture (if exists and not default)
            if (!empty($existing_picture) && file_exists($existing_picture)) {
                @unlink($existing_picture);
            }
            $profile_picture_path = $dest_path;
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error saving uploaded image']);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid image type. Only JPG and PNG allowed']);
        exit;
    }
}

// Update volunteer record
$update = $conn->prepare("UPDATE volunteer SET name = ?, email = ?, region = ?, contact_number = ?, availability = ?, password = ?, profile_picture = ? WHERE volunteer_id = ?");
if (!$update) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$update->bind_param("sssssssi", $name, $email, $region, $contact, $availability, $password_to_store, $profile_picture_path, $id);

if ($update->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully',
        'updated' => [
            'name' => $name,
            'email' => $email,
            'region' => $region,
            'contact_number' => $contact,
            'availability' => $availability,
            'profile_picture' => $profile_picture_path
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database update failed']);
}

$update->close();
$conn->close();
exit;
?>
