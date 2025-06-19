<?php
session_start();
require_once 'backend/db_connect.php';
require_once 'backend/suggested_users.php';
if(!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
  header("Location: loginpage.php");
  exit();
}

function linkify_tags($content) {
  return preg_replace(
    '/#(\w+)/u',
    '<a class="tag-link" href="tag.php?tag=$1">#$1</a>',
    htmlspecialchars($content)
  );
}

$errors = [];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $content = trim($_POST['content']);
  $user_id = $_SESSION['user_id'];

  $media_paths = [];
  if(!empty($_FILES['media']['name'][0])) {
    foreach($_FILES['media']['tmp_name'] as $key => $tmp_name) {
      $file_name = $_FILES['media']['name'][$key];
      $file_tmp = $_FILES['media']['tmp_name'][$key];
      $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
      $new_name = uniqid() . '.' . $file_ext;
      $upload_path = 'uploads/' . $new_name;

      if(move_uploaded_file($file_tmp, $upload_path)) {
        $media_paths[] = $upload_path;
      }
    }
  }
  $media_content = implode(', ', $media_paths);

  if(empty($content) && empty($media_content)) {
    $errors['content'] = "The post cannot be empty.";
  } elseif(empty($content)) {
    $errors['content'] = "The post content cannot be empty.";
  }

  if(empty($errors)) {
    $stmt = $conn->prepare("INSERT INTO post (user_id, text_content, media_content, post_date) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $user_id, $content, $media_content);
    $stmt->execute();
    $post_id = $conn->insert_id;
    $stmt->close();

    preg_match_all('/#(\w+)/u', $content, $matches);
    $tags = array_unique($matches[1]);

    if(!empty($tags)) {
      foreach($tags as $tag) {
        $tag_stmt = $conn->prepare("INSERT IGNORE INTO tag (name) VALUES (?)");
        $tag_stmt->bind_param("s", $tag);
        $tag_stmt->execute();
        $tag_stmt->close();

        $tag_id_stmt = $conn->prepare("SELECT tag_id FROM tag WHERE name = ?");
        $tag_id_stmt->bind_param("s", $tag);
        $tag_id_stmt->execute();
        $tag_id_stmt->bind_result($tag_id);
        $tag_id_stmt->fetch();
        $tag_id_stmt->close();

        $post_tag_stmt = $conn->prepare("INSERT INTO post_tag (post_id, tag_id) VALUES (?, ?)");
        $post_tag_stmt->bind_param("ii", $post_id, $tag_id);
        $post_tag_stmt->execute();
        $post_tag_stmt->close();
      }
    }

    $_SESSION['saved'] = "Post saved successfully.";
  }

  if(!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: homepage.php");
    exit();
  }
}
$user_id = $_SESSION['user_id'];

$feed_tab = '';
if(isset($_GET['feed'])) {
  if($_GET['feed'] === 'following') {
    $feed_tab = 'following';
  } else {
    $feed_tab = 'for_you';
  }
} else {
  $feed_tab = 'for_you';
}

$user = [];
$user_stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
$user_stmt->bind_param("i", $_SESSION['user_id']);
$user_stmt->execute();
$result = $user_stmt->get_result();
while($row = $result->fetch_assoc()) {
  $user = $row;
}
$user_stmt->close();

$_SESSION['username'] = $user['username'];
$_SESSION['avatar'] = $user['avatar'];
$_SESSION['cover'] = $user['cover'];

$liked_post_ids = [];
$like_stmt = $conn->prepare("SELECT post_id FROM `like` WHERE user_id = ?");
$like_stmt->bind_param("i", $user_id);
$like_stmt->execute();
$like_result = $like_stmt->get_result();
while ($like_row = $like_result->fetch_assoc()) {
    $liked_post_ids[] = $like_row['post_id'];
}
$like_stmt->close();


if($feed_tab === 'following') {
  $following_ids=[];
  $follow_stmt = $conn->prepare("SELECT followed_user_id FROM `follow` WHERE following_user_id = ?");
  $follow_stmt->bind_param("i", $user_id);
  $follow_stmt->execute();
  $follow_result = $follow_stmt->get_result();
  while($row = $follow_result->fetch_assoc()) {
    $following_ids[] = $row['followed_user_id'];
  }
  $follow_stmt->close();

  if(!empty($following_ids)) {
    $posts = [];
    $in_placeholders = implode(',', array_fill(0, count($following_ids), '?'));
    $types = str_repeat('i', count($following_ids));
    $query = "
      SELECT p.*, u.username, u.avatar
      FROM post p
      JOIN user u on p.user_id = u.user_id
      WHERE p.user_id IN ($in_placeholders)
      ORDER BY p.post_date DESC
    ";
    $post_stmt = $conn->prepare($query);
    $post_stmt->bind_param($types, ...$following_ids);
    $post_stmt->execute();
    $result = $post_stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $row['liked_by_user'] = in_array($row['post_id'], $liked_post_ids);
      $posts[] = $row;
    }
    $post_stmt->close();
  }

} else {
  $posts = [];
  $post_stmt = $conn->prepare("
    SELECT p.*, u.username, u.avatar
    FROM post p
    JOIN user u on p.user_id = u.user_id
    ORDER BY p.post_date DESC
  ");
  $post_stmt->execute();
  $result = $post_stmt->get_result();
  while($row = $result->fetch_assoc()) {
    $row['liked_by_user'] = in_array($row['post_id'], $liked_post_ids);
    $posts[] = $row;
  }
  $post_stmt->close();
}

$comments = [];
$comment_stmt = $conn->prepare("
  SELECT c.*, u.username, u.avatar
  FROM comment c
  JOIN user u on c.user_id = u.user_id
  ORDER BY c.comment_date DESC
"); 
$comment_stmt->execute();
$result = $comment_stmt->get_result();
while($row = $result->fetch_assoc()) {
  $comments[] = $row;
}
$comment_stmt->close();

if(!empty($errors)) {
  $_SESSION['errors'] = $errors;
  header("Location: homepage.php");
  exit();
}

$trending_tags = [];
$trend_stmt = $conn->prepare("
  SELECT t.name, COUNT(pt.post_id) as post_count
  FROM tag t
  JOIN post_tag pt ON t.tag_id = pt.tag_id
  GROUP BY t.tag_id
  ORDER BY post_count DESC
  Limit 5
");
$trend_stmt->execute();
$trend_stmt->bind_result($tag_name, $tag_post_count);
while($trend_stmt->fetch()) {
  $trending_tags[] = ['name' => $tag_name, 'count' => $tag_post_count];
}
$trend_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <link rel="stylesheet" href="styles/general.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/sidebars.css">
    <link rel="stylesheet" href="styles/feed.css">

    <link
      href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Share+Tech+Mono&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <title>MyGameWorld</title>
  </head>
  <body>
    <header class="top-bar">
      <a class="logo" href="homepage.php">
        <i class="fas fa-gamepad"></i>
        MyGameWorld
      </a>

      <div class="search-container" style="position: relative;">
        <form action="http://localhost/mygamelist/search.php" method="GET" id="searchForm" autocomplete="off">
          <i class="fas fa-search"></i>
          <input
            type="text"
            name="q"
            id="searchInput"
            placeholder="Search posts, users..."
            required
          />
          <div id="searchResultsDropdown" class="search-dropdown" style="display:none; position:absolute; left:0; right:0; background:var(--elements-bg-color); z-index:200;"></div>
        </form>
      </div>
      
      <div class="nav-icons">
        <a class="nav-icon" href="homepage.php">
          <i class="fas fa-home"></i>
        </a >
        <a class="nav-icon" title="Mail" href="messages.php">
          <i class="fas fa-envelope"></i>
          <span class="notification-badge"></span>
        </a>
        <button class="nav-icon" title="Notifications" style="position: relative;">
          <i class="fas fa-bell"></i>
          <span class="notification-badge"></span>
        </button>
        <button id="theme-toggle" class="theme-btn nav-icon" aria-label="Toggle theme">
          <i class="fas fa-sun"></i>
          <i class="fas fa-moon" style="display: none"></i>
        </button>
      </div>

      <div class="user-profile" id="userProfile">
        <span class="username" id="usernameDisplay">
          <?php echo isset($user['username']) ? htmlspecialchars($user['username']) : 'Guest'; ?>
          <i class="fas fa-caret-down"></i>
        </span>
        <div class="dropdown-menu" id="dropdownMenu">
          <a href="userpage.php?id=<?= $_SESSION['user_id'] ?>" class="dropdown-item">
            <i class="fas fa-user"></i>
            <span>Profile</span>
          </a>
          <a href="http://localhost/mygamelist/savedpage.php" class="dropdown-item">
            <i class="fas fa-bookmark"></i>
            <span>Saved</span>
          </a>
          <a href="http://localhost/mygamelist/settingspage.php" class="dropdown-item">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="http://localhost/mygamelist/backend/logout.php" class="dropdown-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Log Out</span>
          </a>
        </div>
        <a href="http://localhost/mygamelist/userpage.php">
          <img src="<?= $user["avatar"] ?>" class="profile-pic" alt="Profile">
        </a>    
      </div>
    </header>

    <div class="container">
      <aside class="sidebar">
        <div class="sidebar-section">
          <a class="user-card" href="userpage.php?id=<?= $_SESSION['user_id'] ?>">
            <img src="<?= $user["avatar"] ?>" alt="Profile">
            <div>
              <div class="post-author"><?php echo isset($user['username']) ? htmlspecialchars($user['username']) : 'Guest'; ?></div>
              <div class="post-time">See your profile</div>
            </div>
          </a>
        </div>
        <div class="sidebar-section">
          <div class="sidebar-title">Shortcuts</div>
          <div class="shortcut-buttons">
            <a class="shortcut-btn" href="userpage.php?id=<?= $_SESSION['user_id'] ?>">
              <i class="fas fa-user"></i> Profile
            </a>
            <a class="shortcut-btn" href="savedpage.php">
              <i class="fas fa-bookmark"></i> Saved Posts
            </a>
            <a class="shortcut-btn" href="settingspage.php">
              <i class="fas fa-cog"></i> Settings
            </a>
            <a class="shortcut-btn" href="messages.php">
              <i class="fas fa-envelope"></i> Messages
            </a>
          </div> 
        </div>
        <div class="sidebar-section">
          <div class="sidebar-title">Trending Tags</div>
          <ul class="trending-tags-list">
            <?php if(empty($trending_tags)): ?>
              <li>No trending tags</li>
            <?php else: ?>
              <?php foreach($trending_tags as $tag): ?>
                <li>
                  <a class="tag-link" href="tag.php?tag=<?= htmlspecialchars($tag['name']) ?>">#<?= htmlspecialchars($tag['name']) ?>
                    (<?= $tag['count'] ?>)
                  </a>
                </li>
              <?php endforeach; ?>
            <?php endif; ?>
          </ul>
        </div>
      </aside>
      <main class="feed">
        <div class="composer">
          <form id="post-form" action="http://localhost/mygamelist/homepage.php" method="POST" enctype="multipart/form-data">
            <?php
            if(isset($_SESSION["errors"])) {
              echo '<div class="error-message">';
              foreach($_SESSION["errors"] as $error) {
                echo '<p>' .  htmlspecialchars($error) . '</p>';
              }
              echo '</div>';
              unset($_SESSION["errors"]);
            } else if(isset($_SESSION["saved"])) {
              echo '<div class="alert success">';
              echo '<p>' .  htmlspecialchars($_SESSION["saved"]) . '</p>';
              echo '</div>';
              unset($_SESSION["saved"]);
            }  
            ?>
            <textarea name="content" id="post-content" placeholder="What's on your mind?" rows="3"></textarea>
            <div class="composer-actions">
              <label for="media-upload" class="composer-action">
                <i class="fas fa-image"></i>
                <input type="file" id="media-upload" multiple name="media[]" accept="image/*,video/mp4" style="display:none;">
              </label>
              <button type="submit" class="post-button">Post</button>
            </div>
            <div id="media-preview" class="media-preview"></div>
          </form>
        </div>
        <div class="feed-sort">
          <a class="sort-option<?= $feed_tab === 'for_you' ? ' active' : '' ?>" href="?feed=for_you">For You</a>
          <a class="sort-option<?= $feed_tab === 'following' ? ' active' : '' ?>" href="?feed=following">Following</a>
        </div>

        <div id="feed-list" class="feed-animated-list">
          <?php if(empty($posts)): ?>
            <div class="no-posts">
              <?php if($feed_tab === 'following'): ?>
                No posts found from people you follow.
              <?php else: ?>
                No posts found. Be the first to post!
              <?php endif ?>
            </div>
          <?php else: ?>
            <?php foreach($posts as $post):
              $post_date = new DateTime($post['post_date'], new DateTimeZone('Europe/Bucharest'));
              $now = new DateTime('now', new DateTimeZone('Europe/Bucharest'));
              $interval = $now->diff($post_date);

              if($interval->y) $time_ago = $interval->y . ' years ago';
              elseif ($interval->m) $time_ago = $interval->m . ' months ago';
              elseif ($interval->d) $time_ago = $interval->d . ' days ago';
              elseif ($interval->h) $time_ago = $interval->h . ' hours ago';
              elseif ($interval->i) $time_ago = $interval->i . ' minutes ago';
              else $time_ago = 'Just now';

              $media_files = $post['media_content'] ? explode(', ', $post['media_content']) : [];
              ?>
              <div class="feed-item">
                <div class="post-header">
                  <a href="userpage.php?id=<?= $post['user_id'] ?>" style="display: contents;">
                    <img src="<?= htmlspecialchars($post['avatar']) ?>" alt="User">
                  </a>
                  <div>
                    <a href="userpage.php?id=<?= $post['user_id'] ?>" class="post-author"><?= htmlspecialchars($post['username']) ?></a>
                    <div class="post-time">
                      <?= $time_ago ?> · <i class="fas fa-globe-americas"></i>
                    </div>
                  </div>
                  <div class="post-menu">
                    <i class="fas fa-ellipsis-h"></i>
                    <div class="post-options">
                      <button class="post-option hide-btn">
                        <span>Hide</span>
                      </button>
                      <button class="post-option report-btn">
                        <span>Report</span>
                      </button>
                    </div>
                  </div>
                </div>

                <div class="post-content">
                  <p class="post-text"><?= linkify_tags($post['text_content']) ?></p>
                  <?php foreach($media_files as $media):
                    if(pathinfo($media, PATHINFO_EXTENSION) === 'mp4'): ?>
                      <video controls class="post-media">
                        <source src="<?= $media ?>" type="video/mp4">
                      </video>
                    <?php else: ?>
                      <img src="<?= $media ?>" alt="Post" class="post-media">
                    <?php endif;
                  endforeach; ?>
                </div>
                <div class="post-stats">
                  <div></div>
                  <div class="like-comment-count" data-like-count="<?= $post['like_count'] ?>" data-comment-count="<?= $post['comment_count'] ?>">
                    <?= $post['like_count'] ?> <i class="fas fa-thumbs-up"></i> <?= $post['comment_count'] ?> comments
                  </div>
                </div>

                <div class="post-action">
                  <a href="#" class="post-action like-btn <?= $post['liked_by_user'] ? ' liked' : '' ?>" data-post-id="<?= $post['post_id'] ?>">
                    <i class="far fa-thumbs-up"></i>
                    <span>Like</span>
                  </a>
                  <div class="post-action comment-trigger">
                    <i class="far fa-comment"></i>
                    <span>Comments</span>
                  </div>
                </div>
                <form method="POST" class="comment-form" style="display: none;">
                  <a class="user-card" href="userpage.php?id=<?= $_SESSION['user_id'] ?>">
                  <img src="<?= $user["avatar"] ?>" alt="Profile">
                  <div>
                    <div class="post-author"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?></div>
                  </div>
                  </a>
                  <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                  <textarea name="comment_content" rows="1" placeholder="Write a comment..."></textarea>
                  <button type="submit" class="post-button">Post</button>
                </form>

                <div class="comments-list" style="display: none;">
                  <?php foreach($comments as $comment):
                    if($comment['post_id'] == $post['post_id']):
                      $comment_date = new DateTime($comment['comment_date'], new DateTimeZone('Europe/Bucharest'));
                      $now = new DateTime('now', new DateTimeZone('Europe/Bucharest'));
                      $interval = $now->diff($comment_date);

                      if($interval->y) $time_ago = $interval->y . ' years ago';
                      elseif ($interval->m) $time_ago = $interval->m . ' months ago';
                      elseif ($interval->d) $time_ago = $interval->d . ' days ago';
                      elseif ($interval->h) $time_ago = $interval->h . ' hours ago';
                      elseif ($interval->i) $time_ago = $interval->i . ' minutes ago';
                      else $time_ago = 'Just now';
                    ?>
                      <div class="comment">
                        <div class="comment-avatar">
                          <a class="user-card" href="userpage.php?id=<?= $comment['user_id'] ?>">
                            <img src="<?= $comment['avatar'] ?>" alt="Profile">
                          </a>
                        </div>
                        <div class="comment-body">
                          <div class="comment-header">
                            <span class="post-author"><?= $comment['username'] ?></span>
                            <span class="post-time"><?= $time_ago ?></span>
                          </div>
                          <div class="comment-text"><?= $comment['content'] ?></div>
                        </div>
                      </div>
                      
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

      </main>
      <aside class="right-sidebar">
        <div class="sidebar-section">
          <div class="sidebar-title">Suggested users</div>
          <ul class="suggested-users-list">
            <?php if(empty($suggested_users)): ?>
              <li>No suggestions</li>
            <?php else: ?>
              <?php foreach($suggested_users as $user): ?>
                <?php if($user['username'] != 'admin'): ?>
                  <li>
                    <a class="user-card" href="userpage.php?id=<?= $user['user_id'] ?>">
                      <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="">
                      <span><?= htmlspecialchars($user['username']) ?></span>
                    </a>
                  </li>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php endif; ?>
          </ul>
        </div>
      </aside>
    </div>
    <script src="scripts/search_live.js"></script>
    <script src="scripts/notifications.js"></script>         
    <script src="scripts/changeThemeScript.js"></script>
    <script>
      const userProfile = document.getElementById("usernameDisplay");
      const dropdownMenu = document.getElementById("dropdownMenu");

      userProfile.addEventListener("click", (e) => {
        e.stopPropagation();
        dropdownMenu.classList.toggle("show");
      });

      document.addEventListener("click", (e) => {
        if(!userProfile.contains(e.target)) {
          dropdownMenu.classList.remove("show");
        }
      });
    </script>
    <script src="scripts/post_menu_trigger.js"></script>
    <script>
      document.getElementById('post-form').addEventListener('submit', function(e) {
        const fileInput = document.getElementById('media-upload');
        const maxSize = 5 * 1024 * 1024; // 5MB in bytes

        for(let file of fileInput.files) {
          if(file.size > maxSize) {
            alert('File size exceeds the maximum limit of 5MB.');
            e.preventDefault();
            return;
          }
        }
      })
    </script>
    <script>
      document.querySelectorAll('.comment-trigger').forEach(trigger => {
        trigger.addEventListener('click', () => {
          const form = trigger.closest('.feed-item').querySelector('.comment-form');
          form.style.display = form.style.display === 'none' ? 'flex' : 'none';
          const commentsList = trigger.closest('.feed-item').querySelector('.comments-list');
          commentsList.style.display = commentsList.style.display === 'none' ? 'block' : 'none';
        });
      });
    </script>
    <script src="scripts/feed_ajax.js"></script>     
    <script src="scripts/preview_media.js"></script>
    <script src="scripts/updateUnreadCount.js"></script>
  </body>
</html>
