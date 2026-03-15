<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed']);
}

$body = read_json_body();
$name = trim($body['name'] ?? '');
$email = trim($body['email'] ?? '');
$password = trim($body['password'] ?? '');

if ($name === '' || $email === '' || $password === '') {
    json_response(422, ['success' => false, 'message' => 'Name, email, and password are required']);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(422, ['success' => false, 'message' => 'Invalid email format']);
}

if (strlen($password) < 6) {
    json_response(422, ['success' => false, 'message' => 'Password must be at least 6 characters']);
}

$nameEsc = mysqli_real_escape_string($conn, $name);
$emailEsc = mysqli_real_escape_string($conn, $email);

$exists = mysqli_query($conn, "SELECT id FROM users WHERE email='$emailEsc' LIMIT 1");
if ($exists && mysqli_num_rows($exists) > 0) {
    json_response(409, ['success' => false, 'message' => 'Email already exists']);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$hashEsc = mysqli_real_escape_string($conn, $hash);

$sql = "INSERT INTO users (name, email, password, is_verified, role, created_at) VALUES ('$nameEsc', '$emailEsc', '$hashEsc', 1, 'staff', NOW())";
if (!mysqli_query($conn, $sql)) {
    json_response(500, ['success' => false, 'message' => 'Failed to create account']);
}

$newId = (int)mysqli_insert_id($conn);
json_response(201, [
    'success' => true,
    'message' => 'Account created successfully',
    'data' => [
        'id' => $newId,
        'name' => $name,
        'email' => $email,
    ],
]);
