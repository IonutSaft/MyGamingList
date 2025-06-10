<?php

session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    error_log('User ID not set in session!');
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

if(!isset($_FILES['cover']) || $_FILES['cover']['error'] !== UPLOAD_ERR_OK) {
  echo json_encode(['success' => false, 'error' => 'No file']);
  exit();
}

$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$max_size = 2 * 1024 * 1024;

$file = $_FILES['cover'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if(!in_array($mime_type, $allowed_types) || !in_array($ext, $allowed_exts)) {
  echo json_encode(['success' => false, 'error' => 'Invalid file type']);
  exit();
}
if($file['size'] > $max_size) {
  echo json_encode(['success' => false, 'error' => 'File too large']);
  exit();
}

$random = bin2hex(random_bytes(16));
$filename = $random . '.' . $ext;
$targetDir = 'cover/';
if(!is_dir('../' . $targetDir)) mkdir($targetDir, 0755, true);
$targetFile = $targetDir . $filename;

if(move_uploaded_file($file['tmp_name'], '../' . $targetFile)) {
  $stmt = $conn->prepare("UPDATE user SET cover = ? WHERE user_id = ?");
  if(!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['success' => false, 'error' => 'Prepare failed']);
    $conn->close();
    exit;
  }
  $stmt->bind_param("si", $targetFile, $user_id);
  $stmt->execute();

  if($stmt->error) {
    error_log('Statement error: ' . $stmt->error);
    echo json_encode(['success' => false, 'error' => 'Statement error']);
    $stmt->close();
    $conn->close();
    exit;
  }

  $stmt->close();
  $conn->close();
  echo json_encode(['success' => true, 'url' => $targetFile]);
} else {
  echo json_encode(['success' => false, 'error' => 'Move failed']);
}

?>