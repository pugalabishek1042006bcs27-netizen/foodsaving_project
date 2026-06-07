<?php
// track_location.php
session_start();
include 'db.php';
if (!isset($_SESSION['volunteer_id'])) { http_response_code(401); exit('Not logged in'); }
$vid = (int)$_SESSION['volunteer_id'];
$lat = isset($_POST['lat']) ? (float)$_POST['lat'] : null;
$lng = isset($_POST['lng']) ? (float)$_POST['lng'] : null;
$heading = isset($_POST['heading']) ? (float)$_POST['heading'] : null;
$speed = isset($_POST['speed']) ? (float)$_POST['speed'] : null;
if (!$lat || !$lng) { http_response_code(400); exit('Missing coords'); }

$stmt = $conn->prepare("INSERT INTO volunteer_locations (volunteer_id, lat, lng, heading, speed) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("idddd", $vid, $lat, $lng, $heading, $speed);
$stmt->execute();
echo json_encode(['status'=>'ok']);
