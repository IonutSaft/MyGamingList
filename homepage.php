<?php
session_start();

if(!isset($_SESSION['loggedin'])) {
  header("Location: loginpage.php");
  exit();
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
          <form id="post-form" action="http://localhost/mygamelist/backend/create_post.php" method="POST" enctype="multipart/form-data">
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
        <div class="feed-item">
          <div class="post-header">
            <img src="default/default_avatar.png" alt="User">
            <div>
              <div class="post-author">TestName</div>
              <div class="post-time">
                3 hours ago . <i class="fas fa-globe-americas"></i>
              </div>
            </div>
            <div class="post-menu">
              <i class="fas fa-ellipsis-h"></i>
            </div>
          </div>
          <div class="post-content">
            <p class="post-text">
              This text is just a placeholder. It will be replaced with the actual post content.
            </p>
            <img src="default/default_cover.png" alt="Post" class="post-image">
          </div>
          <div class="post-stats">
            <div></div>
            <div>
              29 <i class="fas fa-thumbs-up"></i>
              13 comments ·
              5 shares
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
        
        <div class="feed-item">
          <div class="post-header">
            <img src="default/default_avatar.png" alt="User">
            <div>
              <div class="post-author">TestName</div>
              <div class="post-time">
                3 hours ago . <i class="fas fa-globe-americas"></i>
              </div>
            </div>
            <div class="post-menu">
              <i class="fas fa-ellipsis-h"></i>
            </div>
          </div>
          <div class="post-content">
            <p class="post-text">
              This text is just a placeholder. It will be replaced with the actual post content.
            </p>
            <img src="default/default_cover.png" alt="Post" class="post-image">
          </div>
          <div class="post-stats">
            <div></div>
            <div>
              29 <i class="fas fa-thumbs-up"></i>
              13 comments ·
              5 shares
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
    <script src="scripts/preview_media.js"></script>
  </body>
</html>
