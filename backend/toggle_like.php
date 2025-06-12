<?php 
session_start();
require_once 'db_connect.php';

if(!isset($_SESSION['loggedin'])) {
  echo json_encode(['success' => false, 'error' => 'Not logged in']);
  exit();
}

$user_id = (int)$_SESSION['user_id'];
$post_id = (int)($_POST['post_id'] ?? 0);

$stmt = $conn->prepare("SELECT post_id FROM post WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Post not found']);
    exit;
}
$stmt->close();

$stmt = $conn->prepare("SELECT 1 FROM `like` WHERE user_id = ? AND post_id = ?");
$stmt->bind_param("ii", $user_id, $post_id);
$stmt->execute();
$stmt->store_result();
$already_liked = $stmt->num_rows > 0;
$stmt->close();

if ($already_liked) {
    // Unlike: delete like
    $stmt = $conn->prepare("DELETE FROM `like` WHERE user_id = ? AND post_id = ?");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    $stmt->close();

    // Decrement like count
    $stmt = $conn->prepare("UPDATE post SET like_count = like_count - 1 WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $stmt->close();

    $liked = false;
} else {
    // Like: insert like
    $stmt = $conn->prepare("INSERT INTO `like` (user_id, post_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    $stmt->close();

    // Increment like count
    $stmt = $conn->prepare("UPDATE post SET like_count = like_count + 1 WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $stmt->close();

    $liked = true;
}
// Get updated counts
$stmt = $conn->prepare("SELECT like_count, comment_count FROM post WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->bind_result($like_count, $comment_count);
$stmt->fetch();
$stmt->close();

echo json_encode([
    'success' => true,
    'liked' => $liked,
    'like_count' => $like_count,
    'comment_count' => $comment_count
]);
exit;

?>