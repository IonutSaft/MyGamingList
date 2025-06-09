<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if(!isset($_SESSION['loggedin'])) {
  echo json_encode(['success' => false, 'error' => 'Not logged in']);
  exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$post_id = (int)($data['post_id'] ?? 0);

// Verify post ownership
$stmt = $conn->prepare("SELECT user_id FROM post WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if(!$post || $post['user_id'] != $_SESSION['user_id']) {
  echo json_encode(['success' => false, 'error' => 'Unauthorized']);
  exit();
}

// Delete post
$delete_stmt = $conn->prepare("DELETE FROM post WHERE post_id = ?");
$delete_stmt->bind_param("i", $post_id);

$delete_comment = $conn->prepare("DELETE FROM comment WHERE post_id = ?");
$delete_comment->bind_param("i", $post_id);

$delete_like = $conn->prepare("DELETE FROM `like` WHERE post_id = ?");
$delete_like->bind_param("i", $post_id);

if($delete_stmt->execute()) {
  $delete_comment->execute();
  $delete_like->execute();
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'error' => $conn->error]);
}
?>