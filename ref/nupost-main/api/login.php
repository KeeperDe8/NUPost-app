<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed']);
}

$body = read_json_body();
$email = trim($body['email'] ?? '');
$password = trim($body['password'] ?? '');

if ($email === '' || $password === '') {
    json_response(422, ['success' => false, 'message' => 'Email and password are required']);
}

$emailEsc = mysqli_real_escape_string($conn, $email);
$query = mysqli_query($conn, "SELECT id, name, email, password, is_verified FROM users WHERE email='$emailEsc' LIMIT 1");

if (!$query || mysqli_num_rows($query) !== 1) {
    json_response(401, ['success' => false, 'message' => 'Invalid email or password']);
}

$user = mysqli_fetch_assoc($query);
$pwHash = $user['password'] ?? '';
$pwMatch = ($pwHash === $password) || password_verify($password, $pwHash);

if (!$pwMatch) {
    json_response(401, ['success' => false, 'message' => 'Invalid email or password']);
}

if (isset($user['is_verified']) && (int)$user['is_verified'] === 0) {
    json_response(403, ['success' => false, 'message' => 'Please verify your email first']);
}

json_response(200, [
    'success' => true,
    'data' => [
        'id' => (int)$user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
    ],
]);
