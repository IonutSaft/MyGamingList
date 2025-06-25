<?php
session_start();

require_once 'db_connect.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id || !isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
  echo json_encode(['success' => false, 'error' => 'No file']);
  exit;
}

// Settings
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$max_size = 2 * 1024 * 1024; // 2MB

// Validation
$file = $_FILES['avatar'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($mime_type, $allowed_types) || !in_array($ext, $allowed_exts)) {
  echo json_encode(['success' => false, 'error' => 'Invalid file type']);
  exit;
}
if ($file['size'] > $max_size) {
  echo json_encode(['success' => false, 'error' => 'File too large']);
  exit;
}

// Generate random filename
$random = bin2hex(random_bytes(16));
$filename = $random . '.' . $ext;
$targetDir = "avatar/";
if (!is_dir('../' . $targetDir)) mkdir($targetDir, 0755, true);
$targetFile = $targetDir . $filename;

// Move file
if (move_uploaded_file($file['tmp_name'], '../' . $targetFile)) {
  $stmt = $conn->prepare("UPDATE user SET avatar = ? WHERE user_id = ?");
  $stmt->bind_param("si", $targetFile, $user_id);
  $stmt->execute();
  $stmt->close();
  echo json_encode(['success' => true, 'url' => $targetFile]);
} else {
  echo json_encode(['success' => false, 'error' => 'Move failed']);
}

$conn->close();