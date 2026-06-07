<?php
include 'db.php';
header('Content-Type: application/json');
// simple last-locations per volunteer
$sql = "SELECT v.volunteer_id, v.name, vl.lat, vl.lng, vl.created_at
FROM volunteer v
LEFT JOIN (
  SELECT volunteer_id, lat, lng, created_at FROM volunteer_locations vl1
  INNER JOIN (SELECT volunteer_id, MAX(created_at) AS mtime FROM volunteer_locations GROUP BY volunteer_id) t
  ON vl1.volunteer_id = t.volunteer_id AND vl1.created_at = t.mtime
) vl ON v.volunteer_id = vl.volunteer_id";
$res = $conn->query($sql);
$data = [];
while ($r=$res->fetch_assoc()) $data[] = $r;
echo json_encode($data);
