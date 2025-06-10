<?php
session_start();
require_once 'db_connect.php';

$user_id = $_SESSION['user_id'] ?? null;
$bio = trim($_POST['bio'] ?? '');

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

// Optionally limit bio length and strip unwanted tags:
$bio = mb_substr(strip_tags($bio), 0, 500);

$stmt = $conn->prepare("UPDATE user SET description = ? WHERE user_id = ?");
$stmt->bind_param("si", $bio, $user_id);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);
$conn->close();
?>