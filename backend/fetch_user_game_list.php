<?php

session_start();
require_once 'db_connect.php';

$profile_user_id = intval($_POST['profile_user_id'] ?? 0);
$viewer_user_id = $_SESSION['user_id'] ?? 0;
error_log("profile_user_id: " . $profile_user_id);
if(!$profile_user_id) exit;

$stmt = $conn->prepare("
  SELECT gl.game_list_id, g.*, gl.status
  FROM game_list gl JOIN game g on gl.game_id = g.game_id
  WHERE gl.user_id = ?
  ORDER BY gl.game_list_id DESC");
$stmt->bind_param("i", $profile_user_id);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows === 0) {
  echo "<div class='no-games'>No games found.</div>";
  exit;
}

$statuses = ['Playing', 'Completed', 'Dropped', 'Plan to Play'];

echo '<div style="display:flex;flex-wrap:wrap;gap:22px;">';
while($row = $res->fetch_assoc()) {
  echo '<div class="game-card">';
  //remove button for owner
  if($viewer_user_id === $profile_user_id) {
    echo "<button class='remove-game-btn' title='Remove' data-game-list-id='{$row['game_list_id']}'>&times;</button>";
  }
 //cover
  if(!empty($row['cover_url'])) {
    $cover = htmlspecialchars($row['cover_url']);
    echo "<img src='$cover' class='game-cover' alt='Cover'>";
  }

  //title
  echo "<div class='game-title'>" . htmlspecialchars($row['title']) . "</div>";
  //publisher,developer,release date, rating
  if(!empty($row['publisher'])) echo "<div class='game-meta'><strong>Publisher:</strong> " . htmlspecialchars($row['publisher']) . "</div>";
  if(!empty($row['developer'])) echo "<div class='game-meta'><strong>Developer:</strong> " . htmlspecialchars($row['developer']) . "</div>";
  if(!empty($row['release_date'])) echo "<div class='game-meta'><strong>Release:</strong> " . htmlspecialchars($row['release_date']) . "</div>";
  if(!is_null($row['rating'])) echo "<div class='game-meta'><strong>Rating:</strong> " . htmlspecialchars($row['rating']) . "</div>";
  //description
  if(!empty($row['description'])) {
    echo "<div class='game-desc'>" . htmlspecialchars($row['description']) . "</div>";
  }
  //status
  echo '<div class="game-status-row">';
  echo '<span class="game-status-label">Status:</span>';
  if($viewer_user_id === $profile_user_id) {
    echo "<select class='game-status-dropdown' data-game-list-id='{$row['game_list_id']}'>";
    foreach($statuses as $status) {
      $sel = ($row['status'] === $status) ? 'selected' : '';
      echo "<option value='$status' $sel>$status</option>";
    }
    echo "</select>";
  } else {
    echo "<span class='game-status-value'>" . $row['status'] . "</span>";
  }
  echo "</div>";
  echo '</div>';
}
echo '</div>';
?>