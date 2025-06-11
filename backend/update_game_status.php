<?php 
session_start();
require_once 'db_connect.php';

$user_id = $_SESSION['user_id'] ?? 0;
$game_list_id = intval($_POST['game_list_id'] ?? 0);
$status = $_POST['status'] ?? '';
$valid_status = ['Playing', 'Completed', 'Dropped', 'Plan to Play'];
if(!$user_id || !$game_list_id || !in_array($status, $valid_status)) exit;
$stmt = $conn->prepare("UPDATE game_list SET status = ? WHERE game_list_id = ? AND user_id = ?");
$stmt->bind_param("sii", $status, $game_list_id, $user_id);
$stmt->execute();
$stmt->close();
?>