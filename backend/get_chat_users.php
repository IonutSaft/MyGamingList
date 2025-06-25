<?php
session_start();
require_once('db_connect.php');

if(!isset($_SESSION['loggedin'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Not logged in']);
  exit();
}

$user_id = $_SESSION['user_id'];

$sql = "
  SELECT u.user_id, u.username, u.avatar
  FROM user u
  JOIN follow f1 ON f1.followed_user_id = u.user_id AND f1.following_user_id = ?
  JOIN follow f2 ON f2.following_user_id = u.user_id AND f2.followed_user_id = ?
  WHERE u.user_id != ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
  $users[] = $row;
}
$stmt->close();

header('Content-Type: application/json');
echo json_encode($users);
?>