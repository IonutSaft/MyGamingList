<?php 
require_once('db_connect.php');
$q = $_GET['q'];
if (strlen($q) < 2) {
  echo json_encode([]);
  exit;
}
$stmt = $conn->prepare("SELECT game_id, title FROM game WHERE title LIKE CONCAT('%', ?, '%') ORDER BY title LIMIT 10");
$stmt->bind_param("s", $q);
$stmt->execute();
$res = $stmt->get_result();
$games = [];
while($row = $res->fetch_assoc()) {
  $games[] = $row;
}
echo json_encode($games);
?>