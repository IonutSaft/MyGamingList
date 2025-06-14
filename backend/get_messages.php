<?php 
session_start();
require_once('db_connect.php');

if(!isset($_SESSION['loggedin']) ) {
 http_response_code(401);
 echo json_encode(['error' => 'Not logged in']);
 exit();
}

$user_id = $_SESSION['user_id'];
$other_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id']: 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit']: 50;
$offset = isset($_GET['offset']) ? (int)$_GET['offset']: 0;

if($other_user_id <= 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid user ID']);
  exit();
}

$sql = "
  SELECT m.*, u.username, u.avatar
  FROM message m
  JOIN user u ON u.user_id = m.sender_id
  WHERE (m.sender_id = ? AND m.receiver_id = ?)
    OR (m.sender_id = ? AND m.receiver_id = ?)
  ORDER BY m.sent_at DESC
  LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiiii", $user_id, $other_user_id, $other_user_id, $user_id, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
  $messages[] = [
    'message_id' => $row['message_id'],
    'sender_id' => $row['sender_id'],
    'receiver_id' => $row['receiver_id'],
    'content' => $row['content'],
    'sent_at' => $row['sent_at'],
    'username' => $row['username'],
    'avatar' => $row['avatar']
  ];
}
$stmt->close();
header("Content-Type: application/json");
echo json_encode(array_reverse($messages));


?>