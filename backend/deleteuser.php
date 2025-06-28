<?php
session_start();
require_once 'db_connect.php';

if(!isset($_SESSION['loggedin'])) {
  header("Location: loginpage.php");
  exit();
}

if(isset($_SESSION['is_admin'])) {
  $user_id = $_POST['delete_user_id'];
  $admin_id = $_SESSION['user_id'];
} else {
  $user_id = $_SESSION['user_id'];
}

if($user_id === $admin_id) {
  header("Location: ../admin.php?error=cannot_delete_self");
  exit();
}

$del_notif = $conn->prepare("DELETE FROM `notification` WHERE user_id = ?");
$del_notif->bind_param("i", $user_id);
$del_notif->execute();
$del_notif->close();

$del_post = $conn->prepare("DELETE FROM post WHERE user_id = ?");
$del_post->bind_param("i", $user_id);
$del_post->execute();
$del_post->close();

$comm_post_ids = [];
$comm_post = $conn->prepare("SELECT post_id FROM comment WHERE user_id = ?");
$comm_post->bind_param("i", $user_id);
$comm_post->execute();
$result = $comm_post->get_result();
while($row = $result->fetch_assoc()) {
  $comm_post_ids[] = $row['post_id'];
}
$comm_post->close();

if(!empty($comm_post_ids)) {
  foreach($comm_post_ids as $post_id) {
    $post_update = $conn->prepare("UPDATE post SET comment_count = comment_count - 1 WHERE post_id = ?");
    $post_update->bind_param("i", $post_id);
    $post_update->execute();
    $post_update->close();
  }
}

$del_comm = $conn->prepare("DELETE FROM comment WHERE user_id = ?");
$del_comm->bind_param("i", $user_id);
$del_comm->execute();
$del_comm->close();

$post_ids = [];
$like_post = $conn->prepare("SELECT post_id FROM `like` WHERE user_id = ?");
$like_post->bind_param("i", $user_id);
$like_post->execute();
$result = $like_post->get_result();
while ($row = $result->fetch_assoc()) {
    $post_ids[] = $row['post_id'];
}
$like_post->close();

if(!empty($post_ids)) {
  foreach($post_ids as $post_id) {
    $post_update = $conn->prepare("UPDATE post SET like_count = like_count - 1 WHERE post_id = ?");
    $post_update->bind_param("i", $post_id);
    $post_update->execute();
    $post_update->close();
  }
}

$del_like = $conn->prepare("DELETE FROM `like` WHERE user_id = ?");
$del_like->bind_param("i", $user_id);
$del_like->execute();
$del_like->close();

$del_token = $conn->prepare("DELETE FROM token WHERE user_id = ?");
$del_token->bind_param("i", $user_id);
$del_token->execute();
$del_token->close();

$del_gamel = $conn->prepare("DELETE FROM game_list WHERE user_id = ?");
$del_gamel->bind_param("i", $user_id);
$del_gamel->execute();
$del_gamel->close();

$del_followed = $conn->prepare("DELETE FROM follow WHERE followed_user_id = ?");
$del_followed->bind_param("i", $user_id);
$del_followed->execute();
$del_followed->close();

$del_following = $conn->prepare("DELETE FROM follow WHERE following_user_id = ?");
$del_following->bind_param("i", $user_id);
$del_following->execute();
$del_following->close();

$del_message = $conn->prepare("DELETE FROM `message` WHERE sender_id = ? OR receiver_id = ?");
$del_message->bind_param("ii", $user_id, $user_id);
$del_message->execute();
$del_message->close();

$del_user = $conn->prepare("DELETE FROM user WHERE user_id = ?");
$del_user->bind_param("i", $user_id);
$del_user->execute();
$del_user->close();

$conn->close();

if(!isset($_SESSION['is_admin'])) {
  session_destroy();
  header("Location: ../loginpage.php");
  exit();
}

header("Location: ../admin.php");
exit();


?>