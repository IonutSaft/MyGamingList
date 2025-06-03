<?php
$connect = new mysqli("localhost", "root", "", "mygamelist");
if($connect->connect_error) {
  die("Connection failed: " . $connect->connect_error);
}

$connect->set_charset("utf8mb4");

$query = $_GET["query"] ?? "";
$suggestions = [];

if(!empty($query)) {
  $stmt = $connect->prepare("SELECT name from tag WHERE name LIKE CONCAT(?, '%') LIMIT 5");
  $param = $query;
  $stmt->bind_param("s", $param);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()) {
    $suggestions[] = $row["name"];
  }

  $stmt->close();
}

header("Content-Type: application/json");
echo json_encode($suggestions);

?>