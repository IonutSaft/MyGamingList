<?php
session_start();
require_once 'backend/db_connect.php';

if(!isset($_SESSION['loggedin'])) {
  header("Location: loginpage.php");
  exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
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

  $stmt = $conn->prepare("INSERT INTO post (user_id, text_content, media_content, post_date) VALUES (?, ?, ?, NOW())");
  $stmt->bind_param("iss", $user_id, $content, $media_content);
  $stmt->execute();
  $stmt->close();
}

if(isset($_GET['like_post'])) {
  $post_id = (int)$_GET['like_post'];
  $user_id = $_SESSION['user_id'];

  $check_stmt = $conn->prepare("SELECT * FROM `like` WHERE user_id = ? and post_id = ?");
  $check_stmt->bind_param("ii", $user_id, $post_id);
  $check_stmt->execute();

  if($check_stmt->get_result()->num_rows == 0) {
    $like_stmt = $conn->prepare("INSERT INTO `like` (user_id, post_id) VALUES (?, ?)");
    $like_stmt->bind_param("ii", $user_id, $post_id);
    $like_stmt->execute();
    $like_stmt->close();

    $update_stmt = $conn->prepare("UPDATE post SET like_count = like_count + 1 WHERE post_id = ?");
    $update_stmt->bind_param("i", $post_id);
    $update_stmt->execute();
    $update_stmt->close();
  }
  $check_stmt->close();
  header("Location: homepage.php");
  exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'])) {
  $post_id = (int)$_POST['post_id'];
  $user_id = $_SESSION['user_id'];
  $content = trim($_POST['comment_content']);

  if(!empty($content)) {
    $stmt = $conn->prepare("INSERT INTO comment (user_id, post_id, content, comment_date) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $user_id, $post_id, $content);
    $stmt->execute();
    $stmt->close();

    $update_stmt = $conn->prepare("UPDATE post SET comment_count = comment_count + 1 WHERE post_id = ?");
    $update_stmt->bind_param("i", $post_id);
    $update_stmt->execute();
    $update_stmt->close();
  }
}

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
  $posts[] = $row;
}
$post_stmt->close();
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

      <div class="search-container">
        <i class="fas fa-search"></i>
        <input
          type="text"
          placeholder="Search posts, users..."
        />
      </div>
      
      <div class="nav-icons">
        <a class="nav-icon" href="homepage.php">
          <i class="fas fa-home"></i>
        </a >
        <a class="nav-icon" title="Mail" href="#">
          <i class="fas fa-envelope"></i>
          <span class="notification-badge">4</span>
        </a>
        <button class="nav-icon" title="Notifications">
          <i class="fas fa-bell"></i>
          <span class="notification-badge">3</span>
        </button>
        <button id="theme-toggle" class="theme-btn nav-icon" aria-label="Toggle theme">
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
          <img src="<?php echo $_SESSION["avatar"]; ?>" class="profile-pic" alt="Profile">
        </a>    
      </div>
    </header>

    <div class="container">
      <aside class="sidebar">
        <div class="sidebar-section">
          <a class="user-card" href="http://localhost/mygamelist/userpage.php">
            <img src="<?php echo $_SESSION["avatar"]; ?>" alt="Profile">
            <div>
              <div class="post-author"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?></div>
              <div class="post-time">See your profile</div>
            </div>
          </a>
        </div>
        <div class="sidebar-section">
          <div class="sidebar-title">Shortcuts</div>
        </div>
        <div class="sidebar-section">
          <div class="sidebar-title">Trending Games</div>
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
          <div class="sort-option active">For You</div>
          <div class="sort-option">Following</div>
        </div>
        <!-- Aici o sa fie feedul generat -->
        <!-- Exemplu: -->
         
        <?php if(empty($posts)): ?>
          <div class="no-posts">No posts found. Be the first to post!</div>
        <?php else: ?>
          <?php foreach($posts as $post):
            $post_date = new DateTime($post['post_date']);
            $now = new DateTime();
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
                <div class="post-menu">
                  <i class="fas fa-ellipsis-h"></i>
                </div>
              </div>

              <div class="post-content">
                <p class="post-text"><?= htmlspecialchars($post['text_content']) ?></p>
                <?php foreach($media_files as $media):
                  if(pathinfo($media, PATHINFO_EXTENSION) === 'mp4'): ?>
                    <video controls class="post-meida">
                      <source src="<?= $media ?>" type="video/mp4">
                    </video>
                  <?php else: ?>
                    <img src="<?= $media ?>" alt="Post" class="post-media">
                  <?php endif;
                endforeach; ?>
              </div>
              <div class="post-stats">
                <div></div>
                <div>
                  <?= $post['like_count'] ?> <i class="fas fa-thumbs-up"></i> <?= $post['comment_count'] ?> comments
                </div>
              </div>

              <div class="post-action like-btn">
                <a href="homepage.php?like_post=<?= $post['post_id'] ?>" class="post-action">
                  <i class="far fa-thumbs-up"></i>
                  <span>Like</span>
                </a>
                <div class="post-action comment-trigger">
                  <i class="far fa-comment"></i>
                  <span>Comments</span>
                </div>
              </div>
              <form method="POST" class="comment-form" style="display: none;">
                <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                <input type="text" name="comment_content" placeholder="Write a comment..." required>
                <button type="submit" class="post-button">Post</button>
             </form>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

      </main>
      <aside class="right-sidebar">
        <div class="sidebar-section">Suggested users</div>
      </aside>
    </div>

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
          form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });
      });
    </script>
    <script src="scripts/preview_media.js"></script>
  </body>
</html>
