<?php
session_start();

if(!isset($_SESSION['user_id'])) {
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
    <title>MyGameList</title>
  </head>
  <body>
    <header class="top-bar">
      <div class="top-bar-left">
        <a class="site-name" href="homepage.html">MyGameList</a>
        <button class="icon-button">
          <i class="fas fa-home"></i>
        </button>
      </div>
      <div class="top-bar-center">
        <input
          type="text"
          class="search-bar"
          placeholder="Search posts, users..."
        />
      </div>
      <div class="top-bar-right">
        <button id="theme-toggle" class="theme-btn" aria-label="Toggle theme">
          <i class="fas fa-sun"></i>
          <i class="fas fa-moon" style="display: none"></i>
        </button>
        <button class="icon-button" title="Mail">
          <i class="fas fa-envelope"></i>
        </button>
        <button class="icon-button" title="Notifications">
          <i class="fas fa-bell"></i>
        </button>
        <div class="user-profile" id="userProfile">
          <span class="username" id="usernameDisplay">
            <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?>
          </span>
          <div class="dropdown-menu" id="dropdownMenu">
            <a href="" class="dropdown-item">
              <i class="fas fa-user"></i>
              <span>Profile</span>
            </a>
            <a href="" class="dropdown-item">
              <i class="fas fa-bookmark"></i>
              <span>Saved</span>
            </a>
            <a href="" class="dropdown-item">
              <i class="fas fa-cog"></i>
              <span>Settings</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="" class="dropdown-item">
              <i class="fas fa-sign-out-alt"></i>
              <span>Log Out</span>
            </a>
          </div>
          <a href="userpage.html">
            <img src="<?php echo $_SESSION["avatar"]; ?>" class="profile-pic" alt="Profile">
          </a>    
        </div>
        
      </div>
    </header>
    <aside class="sidebar left-sidebar"></aside>
    <main class="main-content"></main>
    <aside class="sidebar right-sidebar"></aside>
    <script src="scripts/changeThemeScript.js"></script>
    <script>
      const userProfile = document.getElementById("usernameDisplay");
      const dropdownMenu = document.getElementById("dropdownMenu");

      userProfile.addEventListener("click", (e) => {
        e.stopPropagation();
        dropdownMenu.classList.toggle("show");
      });

      document.addEventListener("click", () => {
        if(!userProfile.contains(e.target)) {
          dropdownMenu.classList.remove("show");
        }
      });
    </script>
  </body>
</html>
