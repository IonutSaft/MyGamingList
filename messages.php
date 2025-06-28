<?php
session_start();
require_once 'backend/db_connect.php';
require_once 'backend/suggested_users.php';
if(!isset($_SESSION['loggedin'])) {
  header("Location: loginpage.php");
  exit();
}

$user_id = $_SESSION['user_id'];

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

$conn->close();
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
          <?php echo isset($user['username']) ? htmlspecialchars($user['username']) : 'Guest'; ?>
          <i class="fas fa-caret-down"></i>
        </span>
        <div class="dropdown-menu" id="dropdownMenu">
          <a href="userpage.php?id=<?= $_SESSION['user_id'] ?>" class="dropdown-item">
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
          <img src="<?= $user["avatar"] ?>" class="profile-pic" alt="Profile">
        </a>    
      </div>
    </header>

    <div class="container">
      <aside class="sidebar">
        <div class="sidebar-section" id="users">
          <div class="sidebar-title">Chats</div>
        </div>
      </aside>
      <main class="messages-main">
        <div class="chat-container">
          <div class="chatbox" id="chatbox">
            <div class="chat-placeholder"><em>Select a chat to start emssaging.</em></div>
          </div>
          <form id="sendForm" style="display: none;" autocomplete="off">
            <div class="send-msg-row">
              <input type="text" name="msg" id="msg" placeholder="Type a message..." autocomplete="off" maxlength="500" required/>
              <button type="submit" class="send-btn"><i class="fas fa-paper-plane"></i></button>
            </div>
          </form>
        </div>
      </main>
      <aside class="sidebar">
        
      </aside>
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

      document.addEventListener("click", (e) => {
        if(!userProfile.contains(e.target)) {
          dropdownMenu.classList.remove("show");
        }
      });
    </script> 
    <script src="scripts/notifications.js"></script>
    <script src="scripts/messages.js"></script>
    <script src="scripts/updateUnreadCount.js"></script>
  </body>
</html>
