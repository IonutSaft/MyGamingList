<?php
session_start();
require_once('db_connect.php');

if(!isset($_SESSION['loggedin'])) {
  http_response_code(401);
  exit();
}

$user_id = $_SESSION['user_id'];
$other_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

if($other_id <= 0) {
  http_response_code(400);
  exit();
}

$stmt = $conn->prepare("UPDATE message SET read_at = NOW() WHERE sender_id = ? AND receiver_id = ? AND read_at IS NULL");
$stmt->bind_param("ii", $other_id, $user_id);
$stmt->execute();
$stmt->close();
echo json_encode(['success'=>true]);
?>