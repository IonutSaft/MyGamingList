<?php 
session_start();
require_once 'db_connect.php';

if(!isset($_SESSION['loggedin'])) {
  echo json_encode(['success' => false, 'error' => 'Not logged in']);
  exit();
}
$user_id = (int)$_SESSION['user_id'];
$post_id = (int)($_POST['post_id'] ?? 0);
$content = trim($_POST['comment_content'] ?? '');

if(!$post_id || $content =='') {
  echo json_encode(['success' => false, 'error' => 'Invalid input']);
  exit();
}

$stmt = $conn->prepare("SELECT post_id FROM post WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
  echo json_encode(['success' => false, 'error' => 'Post not found']);
  exit;
}
$stmt->close();

// Insert comment
$stmt = $conn->prepare("INSERT INTO comment (post_id, user_id, content, comment_date) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iis", $post_id, $user_id, $content);
$stmt->execute();
$stmt->close();

// Get post owner
$stmt = $conn->prepare("SELECT user_id FROM post WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->bind_result($post_owner_id);
$stmt->fetch();
$stmt->close();

// Don't notify self-comment
if ($user_id !== $post_owner_id) {
  $notif_content = "commented on your post";
  $stmt = $conn->prepare("INSERT INTO `notification` (user_id, actor_id,content, created_at) VALUES (?, ?, ?, NOW())");
  $stmt->bind_param("iis", $post_owner_id, $user_id, $notif_content);
  $stmt->execute();
  $stmt->close();
}

// Increment comment count
$stmt = $conn->prepare("UPDATE post SET comment_count = comment_count + 1 WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare(
  "SELECT c.comment_id, c.user_id, c.content, c.comment_date, u.username, u.avatar
  FROM comment c
  JOIN user u ON c.user_id = u.user_id
  WHERE c.post_id = ?
  ORDER BY c.comment_date DESC
  LIMIT 10"
);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$comments = [];
while ($row = $result->fetch_assoc()) {
  $row['comment_date'] = date('Y-m-d H:i', strtotime($row['comment_date']));
  $comments[] = $row;
}
$stmt->close();

// Get updated counts
$stmt = $conn->prepare("SELECT like_count, comment_count FROM post WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->bind_result($like_count, $comment_count);
$stmt->fetch();
$stmt->close();

echo json_encode([
  'success' => true,
  'comments' => $comments,
  'comment_count' => $comment_count,
  'like_count' => $like_count
]);
exit;
?>