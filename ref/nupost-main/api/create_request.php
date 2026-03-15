<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed']);
}

$isMultipart = stripos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false;

if ($isMultipart || !empty($_POST)) {
    $userId = (int)($_POST['user_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $priority = trim($_POST['priority'] ?? '');
    $preferredDate = trim($_POST['preferred_date'] ?? '');
    $caption = trim($_POST['caption'] ?? '');

    if (isset($_POST['platforms']) && is_array($_POST['platforms'])) {
        $platforms = $_POST['platforms'];
    } elseif (isset($_POST['platforms[]']) && is_array($_POST['platforms[]'])) {
        $platforms = $_POST['platforms[]'];
    } elseif (isset($_POST['platforms_json'])) {
        $decodedPlatforms = json_decode((string)$_POST['platforms_json'], true);
        $platforms = is_array($decodedPlatforms) ? $decodedPlatforms : [];
    } else {
        $platforms = [];
    }
} else {
    $body = read_json_body();
    $userId = (int)($body['user_id'] ?? 0);
    $title = trim($body['title'] ?? '');
    $description = trim($body['description'] ?? '');
    $category = trim($body['category'] ?? '');
    $priority = trim($body['priority'] ?? '');
    $preferredDate = trim($body['preferred_date'] ?? '');
    $caption = trim($body['caption'] ?? '');
    $platforms = $body['platforms'] ?? [];
}

if ($userId <= 0 || $title === '' || $description === '' || $category === '' || $priority === '') {
    json_response(422, ['success' => false, 'message' => 'Missing required fields']);
}

$userQ = mysqli_query($conn, "SELECT name FROM users WHERE id=$userId LIMIT 1");
if (!$userQ || mysqli_num_rows($userQ) !== 1) {
    json_response(404, ['success' => false, 'message' => 'User not found']);
}

$user = mysqli_fetch_assoc($userQ);
$requester = mysqli_real_escape_string($conn, $user['name']);

if (!is_array($platforms)) {
    $platforms = [];
}

$platform = mysqli_real_escape_string($conn, implode(',', $platforms));
$titleEsc = mysqli_real_escape_string($conn, $title);
$descriptionEsc = mysqli_real_escape_string($conn, $description);
$categoryEsc = mysqli_real_escape_string($conn, $category);
$priorityEsc = mysqli_real_escape_string($conn, $priority);
$captionEsc = mysqli_real_escape_string($conn, $caption);
$preferredDateEsc = mysqli_real_escape_string($conn, $preferredDate);

$mediaFileEsc = '';
if (!empty($_FILES)) {
    $fileBucket = $_FILES['media'] ?? $_FILES['media[]'] ?? null;
    if ($fileBucket !== null) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'video/mp4',
            'video/quicktime',
        ];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov'];
        $maxSize = 10 * 1024 * 1024;
        $uploaded = [];

        $names = $fileBucket['name'] ?? [];
        $tmpNames = $fileBucket['tmp_name'] ?? [];
        $types = $fileBucket['type'] ?? [];
        $sizes = $fileBucket['size'] ?? [];
        $errors = $fileBucket['error'] ?? [];

        if (!is_array($names)) {
            $names = [$names];
            $tmpNames = [$tmpNames];
            $types = [$types];
            $sizes = [$sizes];
            $errors = [$errors];
        }

        $count = min(count($names), 4);
        for ($i = 0; $i < $count; $i++) {
            if (($errors[$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
            if (($sizes[$i] ?? 0) > $maxSize) continue;
            $ext = strtolower((string)pathinfo((string)$names[$i], PATHINFO_EXTENSION));
            $type = (string)($types[$i] ?? '');
            $validType = in_array($type, $allowedTypes, true);
            $validExt = in_array($ext, $allowedExtensions, true);
            if (!$validType && !$validExt) continue;

            $newName = uniqid('media_', true) . ($ext ? '.' . $ext : '');
            $dest = $uploadDir . $newName;

            if (move_uploaded_file((string)$tmpNames[$i], $dest)) {
                $uploaded[] = $newName;
            }
        }

        if (!empty($uploaded)) {
            $mediaFileEsc = mysqli_real_escape_string($conn, implode(',', $uploaded));
        }
    }
}

$status = 'Pending';
$sql = "INSERT INTO requests (title, requester, category, priority, status, description, media_file, platform, caption, preferred_date, created_at) VALUES ('$titleEsc', '$requester', '$categoryEsc', '$priorityEsc', '$status', '$descriptionEsc', '$mediaFileEsc', '$platform', '$captionEsc', '$preferredDateEsc', NOW())";

if (!mysqli_query($conn, $sql)) {
    json_response(500, ['success' => false, 'message' => 'Failed to create request']);
}

$newId = (int)mysqli_insert_id($conn);
$reqCode = 'REQ-' . str_pad((string)$newId, 5, '0', STR_PAD_LEFT);
mysqli_query($conn, "UPDATE requests SET request_id='$reqCode' WHERE id=$newId");

json_response(201, [
    'success' => true,
    'data' => [
        'id' => $newId,
        'request_id' => $reqCode,
        'status' => $status,
    ],
]);
