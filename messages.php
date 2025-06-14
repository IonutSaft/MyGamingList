<?php 
session_start();
require_once 'backend/db_connect.php';

if(!isset($_SESSION['loggedin'])) {
  header("Location: loginpage.php");
  exit();
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

$selected_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id']: 0;
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <link rel="stylesheet" href="styles/general.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/sidebars.css">
    <link rel="stylesheet" href="styles/messages.css">
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
          <div class="sidebar-title">Chats</div>
          <ul class="mutuals-list" id="mutualsList"></ul>
          <div class="mutuals-loading" id="mutuals-loading" style="display: none;">Loading...</div>
        </div>
      </aside>
      <main class="messages-main">
        <?php if(!$selected_user_id): ?>
          <div class="messages-placeholder">
            <p>Select a conversation to start messaging.</p>
          </div>
        <?php endif; ?>

        <div id="chatbox" style="display:<?= $selected_user_id ? 'block' : 'none'; ?>;">
          <div class="chatbox-header" id="chatboxUser"></div>
          <div id="chatboxMessages" class="chatbox-messages"></div>
          <form class="chatbox-form" id="chatboxForm">
            <input type="text" name="message" autocomplete="off" placeholder="Type a message...">
            <button type="submit"><i class="fas fa-paper-plane"></i></button>
          </form>
        </div>
      </main>

      <aside class="right-sidebar">
        <div id="chat-profile-card" class="sidebar-section" style="margin-top: 0;">

        </div>
      </aside>
    </div>
    <script>
      window.loggedInUserId = <?= (int)$_SESSION['user_id'] ?>;
      window.selectedUserId = <?= (int)$selected_user_id ?>;
    </script>
    <script src="scripts/messages.js"></script>
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
  </body>
</html>
