<?php
session_start();
require_once 'db_connect.php';
$user_id = $_SESSION['user_id'] ?? 0;
$game_list_id = intval($_POST['game_list_id'] ?? 0);
if(!$user_id || !$game_list_id) {
  echo json_encode(['success' => false, 'error' => 'Invalid input']);
  exit();
}
$stmt = $conn->prepare("DELETE FROM game_list WHERE game_list_id = ? AND user_id = ?");
$stmt->bind_param("ii", $game_list_id, $user_id);
$stmt->execute();
$stmt->close();
echo json_encode(['success' => true]);

?>