<?php
session_start();
require_once('db_connect.php');

if(!isset($_SESSION['loggedin']) ) {
 http_response_code(401);
 echo json_encode(['error' => 'Not logged in']);
 exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id']: 0;
$content = isset($_POST['content']) ? trim($_POST['content']): '';

if($receiver_id <= 0 || empty($content)) {
  http_response_code(400);
  echo json_encode(['error' => 'Missing data']);
  exit();
}

$stmt = $conn->prepare("INSERT INTO message (sender_id, receiver_id, content) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $user_id, $receiver_id, $content);
if($stmt->execute()) {
  echo json_encode([
    'success' => true,
    'message_id' => $stmt->insert_id,
    'sent_at' => date('Y-m-d H:i:s'),
    'content' => htmlspecialchars($content)
  ]);
} else {
  http_response_code(500);
  echo json_encode(['error' => 'Failed to send message']);
}
$stmt->close();
?>