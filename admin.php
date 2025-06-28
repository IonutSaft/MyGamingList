<?php
session_start();
require_once 'backend/db_connect.php';

if(!isset($_SESSION['loggedin'])) {
  header("Location: loginpage.php");
  exit();
}

if(!isset($_SESSION['is_admin'])) {
  header("Location: homepage.php");
  exit();
}

if(isset($_GET['error']) && $_GET['error'] === 'cannot_delete_self') {
  echo '<script>alert("You cannot delete yourself.");</script>';
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  if(isset($_POST['delete_post_id'])) {
    $post_id = intval($_POST['delete_post_id']);
    $delete_stmt = $conn->prepare("DELETE FROM post WHERE post_id = ?");
    $delete_stmt->bind_param("i", $post_id);
    $delete_stmt->execute();
    $delete_stmt->close();

    $delete_comment = $conn->prepare("DELETE FROM comment WHERE post_id = ?");
    $delete_comment->bind_param("i", $post_id);
    $delete_comment->execute();
    $delete_comment->close();

    $delete_like = $conn->prepare("DELETE FROM `like` WHERE post_id = ?");
    $delete_like->bind_param("i", $post_id);
    $delete_like->execute();
    $delete_like->close();
  }

  if(isset($_POST['delete_comment_id'])) {
    $comment_id = intval($_POST['delete_comment_id']);
    $get_post = $conn->prepare("SELECT post_id FROM comment WHERE comment_id = ?");
    $get_post->bind_param("i", $comment_id);
    $get_post->execute();
    $get_post->bind_result($post_id);
    $get_post->fetch();
    $get_post->close();

    if(!empty($post_id)) {
      $delete_stmt = $conn->prepare("DELETE FROM comment WHERE comment_id = ?");
      $delete_stmt->bind_param("i", $comment_id);
      $delete_stmt->execute();
      $delete_stmt->close();

      $recount = $conn->prepare("UPDATE post SET comment_count = comment_count - 1 WHERE post_id = ?");
      $recount->bind_param("i", $post_id);
      $recount->execute();
      $recount->close();
    }
  }
}


$user = $conn->query("SELECT user_id, username, email_address, is_admin FROM user");

echo "<h2>Users</h2>";
echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Email</th><th>Admin</th><th>Action</th></tr>";
while ($row = $user->fetch_assoc()) {
  echo "<tr>";
  echo "<td>".htmlspecialchars($row['user_id'])."</td>";
  echo "<td>".htmlspecialchars($row['username'])."</td>";
  echo "<td>".htmlspecialchars($row['email_address'])."</td>";
  echo "<td>".($row['is_admin'] ? 'Yes' : 'No')."</td>";
  echo "<td>
    <form method='POST' action='backend/deleteuser.php' style='display: inline;'>
      <input type='hidden' name='delete_user_id' value='".htmlspecialchars($row['user_id'])."'>
      <button type='submit' onclick=\"return confirm('Delete this user?');\">Delete</button>
    </form>
  </td>";
  echo "</tr>";
}
echo "</table>";
$user->free();

$post = $conn->query("SELECT post_id, user_id, text_content, media_content, comment_count, like_count, post_date FROM post");

echo "<h2>Posts</h2>";
echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Content</th><th>Media</th><th>Comments</th><th>Likes</th><th>Post date</th><th>Action</th></tr>";
while ($row = $post->fetch_assoc()) {
  echo "<tr>";
  echo "<td>".htmlspecialchars($row['post_id'])."</td>";
  echo "<td>".htmlspecialchars($row['user_id'])."</td>";
  echo "<td>".htmlspecialchars($row['text_content'])."</td>";
  echo "<td>".(!empty($row['media_content']) ? 'Yes' : 'No')."</td>";
  echo "<td>".htmlspecialchars($row['comment_count'])."</td>";
  echo "<td>".htmlspecialchars($row['like_count'])."</td>";
  echo "<td>".htmlspecialchars($row['post_date'])."</td>";
  echo "<td>
    <form method='POST' style='display: inline;'>
      <input type='hidden' name='delete_post_id' value='".htmlspecialchars($row['post_id'])."'>
      <button type='submit' onclick=\"return confirm('Delete this post?');\">Delete</button>
    </form>
  </td>";
  echo "</tr>";
}
echo "</table>";
$post->free();

$comment = $conn->query("SELECT comment_id, post_id, user_id, content, comment_date FROM comment");

echo "<h2>Comments</h2>";
echo "<table border='1'><tr><th>ID</th><th>Post ID</th><th>User ID</th><th>Content</th><th>Comment date</th><th>Action</th></tr>";
while ($row = $comment->fetch_assoc()) {
  echo "<tr>";
  echo "<td>".htmlspecialchars($row['comment_id'])."</td>";
  echo "<td>".htmlspecialchars($row['post_id'])."</td>";
  echo "<td>".htmlspecialchars($row['user_id'])."</td>";
  echo "<td>".htmlspecialchars($row['content'])."</td>";
  echo "<td>".(!(empty($row['comment_date'])) ? htmlspecialchars($row['comment_date']) : 'No date')."</td>";
  echo "<td>
    <form method='POST' style='display: inline;'>
      <input type='hidden' name='delete_comment_id' value='".htmlspecialchars($row['comment_id'])."'>
      <button type='submit' onclick=\"return confirm('Delete this comment?');\">Delete</button>
    </form>
  </td>";
  echo "</tr>";
}
echo "</table>";
$comment->free();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="styles/general.css">
  <title>MyGameWorld</title>
</head>
<body>
  <a href="https://localhost/mygamelist/backend/logout.php" style="text-decoration: none;">
    <i class="fas fa-sign-out-alt"></i>
    <button>Logout</button>
  </a>
</body>
</html>