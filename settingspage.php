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

    <link rel="stylesheet" href="styles/general.css" />
    <link rel="stylesheet" href="styles/header.css" />
    <link rel="stylesheet" href="styles/sidebars.css" />
    <link rel="stylesheet" href="styles/settings.css" />

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
        <form action="https://localhost/mygamelist/search.php" method="GET" id="searchForm" autocomplete="off">
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
          <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?>
          <i class="fas fa-caret-down"></i>
        </span>
        <div class="dropdown-menu" id="dropdownMenu">
          <a href="https://localhost/mygamelist/userpage.php" class="dropdown-item">
            <i class="fas fa-user"></i>
            <span>Profile</span>
          </a>
          <a href="https://localhost/mygamelist/savedpage.php" class="dropdown-item">
            <i class="fas fa-bookmark"></i>
            <span>Saved</span>
          </a>
          <a href="https://localhost/mygamelist/settingspage.php" class="dropdown-item">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="https://localhost/mygamelist/backend/logout.php" class="dropdown-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Log Out</span>
          </a>
        </div>
        <a href="https://localhost/mygamelist/userpage.php">
          <img src="<?php echo $_SESSION["avatar"]; ?>" class="profile-pic" alt="Profile">
        </a>    
      </div>
    </header>

    <div class="container">
      <div class="settings-container">
        <h1 class="settings-title">Account Settings</h1>

        <?php
        if(isset($_SESSION['saved'])) {
          echo '<div class="alert success">';
          echo '<p>' .  htmlspecialchars($_SESSION['saved']) . '</p>';
          echo '</div>';
          unset($_SESSION['saved']);
        } else if(isset($_SESSION['errors'])) {
          echo '<div class="alert error">';
          foreach($_SESSION['errors'] as $error) {
            echo '<p>' .  htmlspecialchars($error) . '</p>';
          }
          echo '</div>';
          unset($_SESSION['errors']);
        }
        ?>

        <form method="POST" class="settings-form" action="https://localhost/mygamelist/backend/updateuser.php">
          <div class="settings-section">
            <div class="settings-item">
              <label for="">Username</label>
              <input type="text" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>
            </div>
            <div class="settings-item">
              <label for="">Email</label>
              <input type="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email_address']); ?>" required>
            </div>

            <div class="settings-buttons">
              <button type="submit" class="settings-btn save-btn">
                <i class="fas fa-save"></i>
                <span>Save Changes</span>
              </button>
              <a href="https://localhost/mygamelist/resetpass.php" class="settings-btn change-password-btn">
                <i class="fas fa-key"></i>
                <span>Change Password</span>
              </a>
              <a class="settings-btn delete-account-btn" href="https://localhost/mygamelist/backend/deleteuser.php" id="deleteAccountBtn">
                <i class="fas fa-trash-alt"></i>
                <span>Delete Account</span>
              </a>
            </div>
          </div>
        </form>

      </div>
    </div>
    <script src="scripts/search_live.js"></script>
    <script src="scripts/changeThemeScript.js"></script>
    <script>
      const userProfile = document.getElementById("usernameDisplay");
      const dropdownMenu = document.getElementById("dropdownMenu");

      userProfile.addEventListener("click", (e) => {
        e.stopPropagation();
        dropdownMenu.classList.toggle("show");
      });

      document.addEventListener("click", () => {
        if (!userProfile.contains(e.target)) {
          dropdownMenu.classList.remove("show");
        }
      });

      document.getElementById("deleteAccountBtn").addEventListener("click", function(e) {
        e.preventDefault();
        if(confirm("Are you sure you want to delete your account? This action cannot be undone.")) {
          window.location.href = this.href;          
        }
      })
    </script>
    <script src="scripts/notifications.js"></script>
    <script src="scripts/updateUnreadCount.js"></script>
  </body>
</html>
