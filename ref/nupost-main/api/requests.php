<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed']);
}

$userId = (int)($_GET['user_id'] ?? 0);
$status = trim($_GET['status'] ?? '');

if ($userId <= 0) {
    json_response(422, ['success' => false, 'message' => 'user_id is required']);
}

$userQ = mysqli_query($conn, "SELECT name FROM users WHERE id=$userId LIMIT 1");
if (!$userQ || mysqli_num_rows($userQ) !== 1) {
    json_response(404, ['success' => false, 'message' => 'User not found']);
}

$user = mysqli_fetch_assoc($userQ);
$requester = mysqli_real_escape_string($conn, $user['name']);

$where = "requester='$requester'";
if ($status !== '' && strtolower($status) !== 'all') {
    $statusEsc = mysqli_real_escape_string($conn, $status);
    $where .= " AND status='$statusEsc'";
}

$sql = "SELECT id, request_id, title, status, created_at FROM requests WHERE $where ORDER BY created_at DESC";
$q = mysqli_query($conn, $sql);

if (!$q) {
    json_response(500, ['success' => false, 'message' => 'Failed to load requests']);
}

$rows = [];
while ($r = mysqli_fetch_assoc($q)) {
    $rows[] = [
        'id' => (int)$r['id'],
        'request_id' => $r['request_id'] ?? '',
        'title' => $r['title'] ?? '',
        'status' => $r['status'] ?? 'Pending',
        'created_at' => $r['created_at'] ?? '',
    ];
}

json_response(200, ['success' => true, 'data' => $rows]);
