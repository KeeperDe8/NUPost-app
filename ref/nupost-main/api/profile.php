<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed']);
}

$userId = (int)($_GET['user_id'] ?? 0);
if ($userId <= 0) {
    json_response(422, ['success' => false, 'message' => 'user_id is required']);
}

$userQ = mysqli_query($conn, "SELECT id, name, email, phone, organization, role, public_profile FROM users WHERE id=$userId LIMIT 1");
if (!$userQ || mysqli_num_rows($userQ) !== 1) {
    json_response(404, ['success' => false, 'message' => 'User not found']);
}
$user = mysqli_fetch_assoc($userQ);

$requester = mysqli_real_escape_string($conn, $user['name']);

$totalQ = mysqli_query($conn, "SELECT COUNT(*) AS c FROM requests WHERE requester='$requester'");
$approvedQ = mysqli_query($conn, "SELECT COUNT(*) AS c FROM requests WHERE requester='$requester' AND status='Approved'");
$pendingQ = mysqli_query($conn, "SELECT COUNT(*) AS c FROM requests WHERE requester='$requester' AND status='Pending'");

$total = $totalQ ? (int)(mysqli_fetch_assoc($totalQ)['c'] ?? 0) : 0;
$approved = $approvedQ ? (int)(mysqli_fetch_assoc($approvedQ)['c'] ?? 0) : 0;
$pending = $pendingQ ? (int)(mysqli_fetch_assoc($pendingQ)['c'] ?? 0) : 0;

json_response(200, [
    'success' => true,
    'data' => [
        'id' => (int)$user['id'],
        'name' => $user['name'] ?? '',
        'email' => $user['email'] ?? '',
        'phone' => $user['phone'] ?? '',
        'organization' => $user['organization'] ?? '',
        'role' => $user['role'] ?? 'staff',
        'public_profile' => (int)($user['public_profile'] ?? 0),
        'stats' => [
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending,
        ],
    ],
]);
