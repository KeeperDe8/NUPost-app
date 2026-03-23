<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed']);
}

$payload = read_json_body();
$userId = (int)($_POST['user_id'] ?? ($payload['user_id'] ?? 0));
$publicProfile = (int)($_POST['public_profile'] ?? ($payload['public_profile'] ?? -1));
$publicCalendar = (int)($_POST['public_calendar'] ?? ($payload['public_calendar'] ?? -1));

if ($userId <= 0) {
    json_response(422, ['success' => false, 'message' => 'user_id is required']);
}

$updates = [];
if ($publicProfile >= 0) {
    $publicProfile = $publicProfile ? 1 : 0;
    $updates[] = "public_profile=$publicProfile";
}

if ($publicCalendar >= 0) {
    $publicCalendar = $publicCalendar ? 1 : 0;
    $updates[] = "public_calendar=$publicCalendar";
}

if (empty($updates)) {
    json_response(422, ['success' => false, 'message' => 'No fields to update']);
}

$updateSet = implode(', ', $updates);
$query = "UPDATE users SET $updateSet WHERE id=$userId LIMIT 1";
if (mysqli_query($conn, $query)) {
    $response = ['success' => true, 'message' => 'Profile updated', 'data' => []];
    if ($publicProfile >= 0) {
        $response['data']['public_profile'] = $publicProfile;
    }
    if ($publicCalendar >= 0) {
        $response['data']['public_calendar'] = $publicCalendar;
    }
    json_response(200, $response);
} else {
    json_response(500, ['success' => false, 'message' => 'Failed to update profile']);
}

