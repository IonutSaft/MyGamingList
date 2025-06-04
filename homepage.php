<?php

session_start();

if(!isset($_SESSION['loggedin'])) {
  header("Location: loginpage.php");
  exit();
}

$conn = new mysqli("localhost", "root", "", "mygamelist");
if($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

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
          <form id="post-form" action="http://localhost/mygamelist/backend/post_handler.php" method="POST" enctype="multipart/form-data">
            <textarea name="post-content" id="post-content" placeholder="What's on your mind?" rows="3"></textarea>
            <div id="tag-suggestions" class="tag-suggestions"></div>
            <div class="composer-actions">
              <label for="media-upload" class="composer-action">
                <i class="fas fa-image"></i>
                <input type="file" id="media-upload" multiple name="media[]" accept="image/*,video/*" style="display:none;">
              </label>
              <div class="composer-action" id="tag-button">
                <i class="fas fa-tag"></i>
              </div>
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
        
        <?php
        function time_elapsed_string($datetime) {
          $now = new DateTime;
          $ago = new DateTime($datetime);
          $diff = $now->diff($ago);

          if($diff->d > 0) {
            return $diff->d . ' days ago';
          } elseif ($diff->h > 0) {
            return $diff->h . ' hours ago';
          } elseif ($diff->i > 0) {
            return $diff->i . ' minutes ago';
          } else {
            return 'just now';
          }
        }

        $query = "SELECT p.*, u.username, u.avatar
                  FROM post p
                  JOIN user u ON p.user_id = u.user_id
                  ORDER BY p.post_date DESC";
        $result = mysqli_query($conn, $query);

        if(mysqli_num_rows($result) > 0) {
          while ($row = mysqli_fetch_assoc($result)) {
            $avatar = !empty($row['avatar']) ? $row['avatar'] : 'default/default_avatar.png';
            $relative_time = time_elapsed_string($row['post_date']);
            ?>
            <div class="feed-item">
              <div class="post-header">
                <img src="<?= htmlspecialchars($avatar) ?>" alt="User">
                <div>
                  <div class="post-author"><?= htmlspecialchars($row['username']) ?></div>
                  <div class="post-time">
                    <?= $relative_time ?> . <i class="fas fa-globe-americas"></i>
                  </div>
                </div>
                <div class="post-menu">
                  <i class="fas fa-ellipsis-h"></i>
                </div>
              </div>
              <div class="post-content">
                <p class="post-text">
                  <?= htmlspecialchars($row['text_content']) ?>
                </p>
                <?php if (!empty($row['media_content'])) : ?>
                  <img src="<?= htmlspecialchars($row['media_content']) ?>" alt="Post" class="post-image">
                <?php endif; ?>  
              </div>
              <div class="post-stats">
                <div></div>
                <div>
                  <?= $row['like_count'] ?> <i class="fas fa-thumbs-up"></i>
                  <?= $row['comment_count'] ?> comments ·
                  <?= $row['shares_count'] ?> shares
                </div>
              </div>
              <div class="post-actions">
                <div class="post-action">
                  <i class="far fa-thumbs-up"></i>
                  <span>Like</span>
                </div>
                <div class="post-action">
                  <i class="far fa-comment"></i>
                  <span>Comment</span>
                </div>
                <div class="post-action">
                  <i class="fas fa-share"></i>
                  <span>Share</span>
                </div>
              </div>
            </div>
            <?php
          }
        } else {
          echo '<div class="feed-item">No posts found. Be the first to post!</div>';
        }
        ?>
        
      </main>
      <aside class="right-sidebar">
        <div class="sidebar-section">Recent Posts</div>
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
    <script src="scripts/create_post.js"></script>
  </body>
</html>
