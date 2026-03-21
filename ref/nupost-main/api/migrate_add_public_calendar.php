<?php
/**
 * Migration: Add public_calendar column to users table
 * Run this script once to add the public_calendar column if it doesn't exist
 * Then delete or comment out the reference in your setup
 */

require_once __DIR__ . '/../config/database.php';

// Check if public_calendar column exists
$checkQuery = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
              WHERE TABLE_NAME='users' AND COLUMN_NAME='public_calendar' AND TABLE_SCHEMA=DATABASE()";
$result = mysqli_query($conn, $checkQuery);

if (mysqli_num_rows($result) === 0) {
    // Column doesn't exist, add it
    $alterQuery = "ALTER TABLE users ADD COLUMN public_calendar TINYINT(1) DEFAULT 0 AFTER public_profile";
    
    if (mysqli_query($conn, $alterQuery)) {
        echo json_encode([
            'success' => true,
            'message' => 'public_calendar column added to users table'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add public_calendar column: ' . mysqli_error($conn)
        ]);
    }
} else {
    echo json_encode([
        'success' => true,
        'message' => 'public_calendar column already exists'
    ]);
}

mysqli_close($conn);
?>
