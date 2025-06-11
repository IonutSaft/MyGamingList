<?php
session_start();
require_once('db_connect.php');
$user_id = $_SESSION['user_id'] ?? 0;
$profile_user_id = intval($_POST['profile_user_id'] ?? 0);
$game_id = intval($_POST['game_id'] ?? 0);
if(!$user_id || $user_id != $profile_user_id || !$game_id) {
  echo json_encode(['success' => false, 'error' => 'Invalid input']);
  exit();
}
$stmt = $conn->prepare("INSERT IGNORE INTO game_list (user_id, game_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $game_id);
$stmt->execute();
$stmt->close();
echo json_encode(['success' => true]);
?>