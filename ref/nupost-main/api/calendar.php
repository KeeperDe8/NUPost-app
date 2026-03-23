<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed']);
}

$userId = (int)($_GET['user_id'] ?? 0);
$month = (int)($_GET['month'] ?? date('m'));
$year = (int)($_GET['year'] ?? date('Y'));
$publicView = (int)($_GET['public'] ?? 0) === 1;

if ($userId <= 0) {
    json_response(422, ['success' => false, 'message' => 'user_id is required']);
}

if ($month < 1 || $month > 12) {
    $month = date('m');
}
if ($year < 2000 || $year > 2100) {
    $year = date('Y');
}

// Get user name for querying requests
$userQ = mysqli_query($conn, "SELECT name FROM users WHERE id=$userId LIMIT 1");
if (!$userQ || mysqli_num_rows($userQ) !== 1) {
    json_response(404, ['success' => false, 'message' => 'User not found']);
}
$user = mysqli_fetch_assoc($userQ);
$requester = mysqli_real_escape_string($conn, $user['name']);

// Fetch requests for this month
// Public view: only scheduled dates (preferred_date)
// Private view: BOTH created_at (request date) AND preferred_date (scheduled date)
if ($publicView) {
    $query = "
        SELECT
            r.id,
            r.title,
            r.status,
            r.preferred_date,
            r.requester
        FROM requests r
        INNER JOIN users u ON r.requester = u.name
        WHERE r.preferred_date IS NOT NULL
        AND r.preferred_date != ''
        AND MONTH(r.preferred_date) = $month
        AND YEAR(r.preferred_date) = $year
        AND u.public_calendar = 1
        ORDER BY r.preferred_date ASC
    ";
} else {
    $query = "
        SELECT
            id,
            title,
            status,
            created_at,
            preferred_date
        FROM requests
        WHERE requester = '$requester'
        AND (
            (MONTH(created_at) = $month AND YEAR(created_at) = $year)
            OR
            (preferred_date IS NOT NULL AND preferred_date != ''
             AND MONTH(preferred_date) = $month AND YEAR(preferred_date) = $year)
        )
        ORDER BY created_at ASC
    ";
}

$result = mysqli_query($conn, $query);
if (!$result) {
    json_response(500, ['success' => false, 'message' => 'Database query failed']);
}

// Build post list with both dates for private view
$posts = [];
while ($row = mysqli_fetch_assoc($result)) {
    $posts[] = [
        'id' => (int)$row['id'],
        'title' => $row['title'] ?? '',
        'status' => $row['status'] ?? 'Pending',
        'request_date' => $publicView ? null : ($row['created_at'] ?? ''),
        'scheduled_date' => $row['preferred_date'] ?? '',
        'requester' => $publicView ? ($row['requester'] ?? '') : null,
    ];
}

json_response(200, [
    'success' => true,
    'data' => [
        'month' => $month,
        'year' => $year,
        'posts' => $posts,
        'public_view' => $publicView,
    ],
]);

