<?php 
session_start();
require_once 'db_connect.php';

$type = $_GET['type'] ?? '';
$user_id = intval($_GET['user_id'] ?? 0);
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

if($type === 'followers') {
  $count_stmt = $conn->prepare("SELECT COUNT(*) FROM follow WHERE followed_user_id = ?");
} else if ($type === 'following') {
  $count_stmt = $conn->prepare("SELECT COUNT(*) FROM follow WHERE following_user_id = ?");
} else {
  echo "Invalid request.";
  exit();
}

$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_stmt->bind_result($total_count);
$count_stmt->fetch();
$count_stmt->close();
$total_pages = ceil($total_count / $per_page);

if($type === 'followers') {
  $stmt = $conn->prepare (
    "SELECT u.user_id, u.username, u.avatar
    FROM follow f JOIN user u ON f.following_user_id = u.user_id
    WHERE f.followed_user_id = ?
    ORDER BY u.username
    LIMIT ? OFFSET ?"
  );
} else {
  $stmt = $conn->prepare (
    "SELECT u.user_id, u.username, u.avatar
    FROM follow f JOIN user u ON f.followed_user_id = u.user_id
    WHERE f.following_user_id = ?
    ORDER BY u.username
    LIMIT ? OFFSET ?"
  );
}
$stmt->bind_param("iii", $user_id, $per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

echo "<ul style='list-style: none; padding: 0;'>";
while($row = $result->fetch_assoc()) {
  $avatar = htmlspecialchars($row['avatar']);
  $username = htmlspecialchars($row['username']);
  $id = $row['user_id'];
  $isFollowing = false;
  if(isset($_SESSION['user_id']) && $_SESSION['user_id'] !== $id) {
    $follow_stmt = $conn->prepare(
      "SELECT 1 FROM follow WHERE following_user_id = ? AND followed_user_id = ?"
    );
    $follow_stmt->bind_param("ii", $_SESSION['user_id'], $id);
    $follow_stmt->execute();
    $follow_stmt->store_result();
    $isFollowing = $follow_stmt->num_rows > 0;
    $follow_stmt->close();
  }
  echo "<li style='margin-bottom:10px;display:flex;align-items:center;'>
          <img src='$avatar' alt='' style='width:32px;height:32px;border-radius:50%;margin-right:8px;'>
          <a href='userpage.php?id=$id' style='margin-right:10px;text-decoration:none;font-family:inherit;color:inherit;'>$username</a>";
  echo "</li>";
}
echo "</ul>";

echo "<!--PAGINATION-->";
if($total_pages > 1) {
  echo 'div style="text-align:center;margin-top:10px;">';
  for($i = 1; $i <= $total_pages; $i++) {
    if($i == $page) {
      echo "<strong>$i</strong>";
    } else {
      echo "<a href='#' class='page-link' data-page='$i'>$i</a>";
    }
  }
  echo "</div>";
}

$stmt->close();
$conn->close();
?>