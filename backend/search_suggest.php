<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');
$q = trim($_GET['q'] ?? '');

$result = ['users' => [], 'posts' => []];

if($q !== '') {
  $user_stmt = $conn->prepare("SELECT user_id, username, avatar FROM user WHERE username LIKE ? LIMIT 5");
  $like = '%' . $q . '%';
  $user_stmt->bind_param("s", $like);
  $user_stmt->execute();
  $user_stmt->bind_result($user_id, $username, $avatar);
  while($user_stmt->fetch()) {
    $result['users'][] = ['user_id' => $user_id, 'username' => $username, 'avatar' => $avatar];
  }
  $user_stmt->close();

  $post_stmt = $conn->prepare("SELECT post_id, text_content FROM post WHERE text_content LIKE ? LIMIT 5");
  $post_stmt->bind_param("s", $like);
  $post_stmt->execute();
  $post_stmt->bind_result($post_id, $text_content);
  while($post_stmt->fetch()) {
    $result['posts'][] = ['post_id' => $post_id, 'text_content' => $text_content];
  }
  $post_stmt->close();
}

echo json_encode($result);
?>