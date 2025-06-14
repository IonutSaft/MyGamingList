<?php

if(!isset($_SESSION['loggedin'])) {
  header("Location: loginpage.php");
  exit();
}

$user_id = $_SESSION['user_id'];

$suggested_stmt = $conn->prepare("
  SELECT user_id, username, avatar FROM user
  WHERE user_id != ?
    AND user_id NOT IN (SELECT followed_user_id FROM follow WHERE following_user_id = ?)
  ORDER BY RAND()  
  LIMIT 5
");
$suggested_stmt->bind_param("ii", $user_id, $user_id);
$suggested_stmt->execute();
$suggested_result = $suggested_stmt->get_result();
$suggested_users = [];
while($row = $suggested_result->fetch_assoc()) {
  $suggested_users[] = $row;
}
$suggested_stmt->close();

?>