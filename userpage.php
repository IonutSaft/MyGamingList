<?php
session_start();
require_once 'backend/db_connect.php';
require_once 'backend/suggested_users.php';
if(!isset($_SESSION['loggedin'])) {
  header("Location: loginpage.php");
  exit();
}


$current_user_id = $_SESSION['user_id'];
$profile_user_id = isset($_GET['id']) ? (int)$_GET['id'] : $current_user_id;

$is_own_profile = ($current_user_id == $profile_user_id);


$stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt->bind_param("i", $profile_user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile_user = $result->fetch_assoc();


if(!$profile_user) {
  die("User not found");
  exit();
}

$post_count = $conn->query("SELECT COUNT(*) FROM post WHERE user_id = $profile_user_id")->fetch_row()[0];
$follower_count = $conn->query("SELECT COUNT(*) FROM follow WHERE followed_user_id = $profile_user_id")->fetch_row()[0];
$following_count = $conn->query("SELECT COUNT(*) FROM follow WHERE following_user_id = $profile_user_id")->fetch_row()[0];

$is_following = false;
if(!$is_own_profile) {
  $check_follow = $conn->prepare("SELECT * FROM follow WHERE following_user_id = ? AND followed_user_id = ?");
  $check_follow->bind_param("ii", $current_user_id, $profile_user_id);
  $check_follow->execute();

  if($check_follow->get_result()->num_rows > 0) {
    $is_following = true;
  } else {
    $is_following = false;
  }

  $check_follow->close();
}

$stmt->close();

$liked_post_ids = [];
$like_stmt = $conn->prepare("SELECT post_id FROM `like` WHERE user_id = ?");
$like_stmt->bind_param("i", $current_user_id);
$like_stmt->execute();
$like_result = $like_stmt->get_result();
while ($like_row = $like_result->fetch_assoc()) {
    $liked_post_ids[] = $like_row['post_id'];
}
$like_stmt->close();

$posts=[];
$post_stmt = $conn->prepare("
  SELECT p.*, u.username, u.avatar
  FROM post p
  JOIN user u on p.user_id = u.user_id
  WHERE p.user_id = ?
  ORDER BY p.post_date DESC
");
$post_stmt->bind_param("i", $profile_user_id);
$post_stmt->execute();
$result = $post_stmt->get_result();
while($row = $result->fetch_assoc()) {
  $row['liked_by_user'] = in_array($row['post_id'], $liked_post_ids);
  $posts[] = $row;
}
$post_stmt->close();

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
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="stylesheet" href="styles/userpage.css">
    <link rel="stylesheet" href="styles/general.css" />
    <link rel="stylesheet" href="styles/header.css" />
    <link rel="stylesheet" href="styles/sidebars.css" />
    <link rel="stylesheet" href="styles/feed.css" />

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

      <div class="search-container">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search posts, users..." />
      </div>

      <div class="nav-icons">
        <a class="nav-icon" href="homepage.php">
          <i class="fas fa-home"></i>
        </a>
        <a class="nav-icon" title="Mail" href="messages.php">
          <i class="fas fa-envelope"></i>
          <span class="notification-badge"></span>
        </a>
        <button class="nav-icon" title="Notifications" style="position: relative;">
          <i class="fas fa-bell"></i>
          <span class="notification-badge"></span>
        </button>
        <button
          id="theme-toggle"
          class="theme-btn nav-icon"
          aria-label="Toggle theme"
        >
          <i class="fas fa-sun"></i>
          <i class="fas fa-moon" style="display: none"></i>
        </button>
      </div>

      <div class="user-profile" id="userProfile">
        <span class="username" id="usernameDisplay">
          <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?>
          <i class="fas fa-caret-down"></i>
        </span>
        <div class="dropdown-menu" id="dropdownMenu">
          <a href="http://localhost/mygamelist/userpage.php" class="dropdown-item">
            <i class="fas fa-user"></i>
            <span>Profile</span>
          </a>
          <a
            href="http://localhost/mygamelist/savedpage.php"
            class="dropdown-item"
          >
            <i class="fas fa-bookmark"></i>
            <span>Saved</span>
          </a>
          <a
            href="http://localhost/mygamelist/settingspage.php"
            class="dropdown-item"
          >
            <i class="fas fa-cog"></i>
            <span>Settings</span>
          </a>
          <div class="dropdown-divider"></div>
          <a
            href="http://localhost/mygamelist/backend/logout.php"
            class="dropdown-item"
          >
            <i class="fas fa-sign-out-alt"></i>
            <span>Log Out</span>
          </a>
        </div>
        <a href="http://localhost/mygamelist/userpage.php">
          <img src="<?php echo $_SESSION["avatar"]; ?>" class="profile-pic"
          alt="Profile">
        </a>
      </div>
    </header>

    <div class="container">
      <aside class="sidebar">
        <div class="sidebar-section">
          <a class="user-card" href="http://localhost/mygamelist/userpage.php">
            <img src="<?php echo $_SESSION["avatar"]; ?>" alt="Profile">
            <div>
              <div class="post-author">
                <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?>
              </div>
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
          <div class="sidebar-title">Trending Games</div>
        </div>
      </aside>
      <main class="user">
        <div class="profile-header">
          <div class="cover-photo">
            <img src="<?= htmlspecialchars($profile_user['cover'] ?? '') ?>" alt="Cover Photo" id="cover-image">
            <?php if($is_own_profile): ?>
              <form class="image-upload-form" id="cover-form">
                <label for="cover-upload" class="edit-cover">
                  <i class="fas fa-camera"></i> Edit Cover
                  <input name="cover" type="file" id="cover-upload" accept="image/*" style="display:none;">
                </label>
              </form>
            <?php endif; ?>
          </div>

          <div class="profile-info">
            <div class="profile-main">
              <div class="avatar-container">
                <img src="<?= htmlspecialchars($profile_user['avatar'] ?? '') ?>" alt="Profile Picture" class="profile-avatar" id="avatar-image">
                <?php if($is_own_profile): ?>
                  <form class="image-upload-form" id="avatar-form">
                    <label for="avatar-upload" class="edit-avatar">
                      <i class="fas fa-camera"></i>
                      <input name="avatar" type="file" id="avatar-upload" accept="image/*" style="display:none;">
                    </label>
                  </form>
                <?php endif; ?>
              </div>

              <div class="profile-details">
                <h1 class="profile-name"><?= htmlspecialchars($profile_user['username'] ?? 'Error') ?></h1>
                <div class="profile-bio-container">
                  <?php if($is_own_profile): ?>
                    <textarea id="profile-bio" class="editable-bio" placeholder="Tell others about yourself..."><?= htmlspecialchars($profile_user['description'] ?? '') ?></textarea>
                    <button class="save-btn" id="save-bio">Save</button>
                  <?php else: ?>
                    <p class="profile-bio"><?= htmlspecialchars($profile_user['description'] ?? '') ?></p>
                  <?php endif; ?>
                </div>
                <div class="profile-stats">
                  <div class="stat">
                    <span class="stat-number"><?= $post_count ?></span>
                    <span class="stat-label">Posts</span>
                  </div>
                  <div class="stat">
                    <span class="stat-number"><?= $follower_count ?></span>
                    <button id="show-followers" class="stat-label">Followers</button>
                  </div>
                  <div class="stat">
                    <span class="stat-number"><?= $following_count ?></span>
                    <button id="show-following" class="stat-label">Following</button>
                  </div>
                  <div class="modal" id="follow-modal" style="display: none;">
                    <div class="modal-content">
                      <span class="close" id="close-modal">&times;</span>
                      <div id="modal-title"></div>
                      <div id="modal-body"></div>
                      <div id="modal-pagination"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="profile-actions">
              <?php if(!$is_own_profile): ?>
                <button class="follow-btn <?= $is_following ? 'following' : '' ?>" data-user-id="<?= $profile_user_id ?>">
                  <?= $is_following ? 'Following' : 'Follow' ?>
                </button>
                <button class="report-btn">
                  <i class="fas fa-flag"></i>
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Profile Navigation -->
        <div class="profile-nav">
          <button class="profile-nav-btn active" data-target="posts-content">
            <i class="fas fa-scroll"></i> Posts
          </button>
          <button class="profile-nav-btn" data-target="games-content">
            <i class="fas fa-gamepad"></i> Games
          </button>
        </div>

        <!-- Posts Section (visible by default) -->
        <div class="profile-content active" id="posts-content">
          <?php if(empty($posts)): ?>
            <div class="no-posts">
              <i class="fas fa-scroll"></i>
              <p><?= $is_own_profile ? 'You haven' : htmlspecialchars($profile_user['username']) . ' hasn' ?>'t posted yet</p>
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
                  <img src="<?= htmlspecialchars($post['avatar']) ?>" alt="User">
                  <div>
                    <div class="post-author"><?= htmlspecialchars($post['username']) ?></div>
                    <div class="post-time">
                      <?= $time_ago ?> · <i class="fas fa-globe-americas"></i>
                    </div>
                  </div>
                  <?php if($is_own_profile): ?>
                    <div class="post-menu" id="postMenu">
                      <i class="fas fa-ellipsis-h"></i>
                      <div class="post-options" id="postOptions">
                        <button class="post-option delete-post" data-post-id="<?= $post['post_id'] ?>">
                          <i class="fas fa-trash"></i>Delete
                          
                        </button>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="post-content">
                  <p class="post-text"><?= htmlspecialchars($post['text_content']) ?></p>
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
                  <img src="<?php echo $_SESSION["avatar"]; ?>" alt="Profile">
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

        <!-- Games Section (hidden by default) -->
        <div id="add-game-modal" class="modal" style="display: none;">
          <div class="modal-content">
            <span class="close" id="close-add-game-modal">&times;</span>
            <h3>Add a Game</h3>
            <input type="text" id="game-search-input" placeholder="Search for a game...">
            <div id="game-search-results"></div>
          </div>
        </div>
        <div class="profile-content" id="games-content">
          <?php if($_SESSION['user_id'] == $profile_user['user_id']): ?>
            <button id="add-game-btn">Add Game</button>
          <?php endif; ?>
          <div class="games-list" id="user-game-list"></div>
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
                <li>
                  <a class="user-card" href="userpage.php?id=<?= $user['user_id'] ?>">
                    <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="">
                    <span><?= htmlspecialchars($user['username']) ?></span>
                  </a>
                </li>
              <?php endforeach; ?>
            <?php endif; ?>
          </ul>
        </div>
      </aside>
    </div>
    <script>
      window.profileUserId = <?= (int)$profile_user['user_id'] ?>;
      window.currentUserId = <?= (int)$_SESSION['user_id'] ?>;
    </script>

    <script src="scripts/user_game_list.js"></script>
    <script src="scripts/followModal.js"></script>
    <script src="scripts/profileEdit.js"></script>
    <script src="scripts/changeThemeScript.js"></script>
    <script>
      const userProfile = document.getElementById("usernameDisplay");
      const dropdownMenu = document.getElementById("dropdownMenu");

      userProfile.addEventListener("click", (e) => {
        e.stopPropagation(e);
        dropdownMenu.classList.toggle("show");
      });

      document.addEventListener("click", (e) => {
        if (!userProfile.contains(e.target)) {
          dropdownMenu.classList.remove("show");
        }
      });
    </script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const navButtons = document.querySelectorAll('.profile-nav-btn');
        const contentSections = document.querySelectorAll('.profile-content');
        
        navButtons.forEach(button => {
          button.addEventListener('click', function() {
            // Remove active class from all buttons and sections
            navButtons.forEach(btn => btn.classList.remove('active'));
            contentSections.forEach(section => section.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Show corresponding content section
            const targetId = this.getAttribute('data-target');
            document.getElementById(targetId).classList.add('active');
          });
        });
      });
    </script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const navButtons = document.querySelectorAll('.profile-nav-btn');
        const contentSections = document.querySelectorAll('.profile-content');
        
        navButtons.forEach(button => {
          button.addEventListener('click', function() {
            // Don't do anything if this tab is already active
            if (this.classList.contains('active')) return;
            
            // Remove active class from all buttons
            navButtons.forEach(btn => btn.classList.remove('active'));
            
            // Fade out current active section
            const currentActive = document.querySelector('.profile-content.active');
            if (currentActive) {
              currentActive.style.opacity = '0';
              currentActive.style.transform = 'translateY(10px)';
              setTimeout(() => {
                currentActive.classList.remove('active');
              }, 300); // Match this with CSS transition duration
            }
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Show corresponding content section with fade-in
            const targetId = this.getAttribute('data-target');
            const targetSection = document.getElementById(targetId);
            setTimeout(() => {
              targetSection.classList.add('active');
              // Force reflow to trigger animation
              void targetSection.offsetWidth;
              targetSection.style.opacity = '1';
              targetSection.style.transform = 'translateY(0)';
            }, 300);
          });
        });
      });
    </script>
    <script src="scripts/follow.js"></script>
    <script src="scripts/post_delete.js"></script>
    <script src="scripts/post_menu_trigger.js"></script>
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
    <script src="scripts/notifications.js"></script>
    <script src="scripts/feed_ajax.js"></script>
    <script src="scripts/updateUnreadCount.js"></script>
  </body>
</html>
