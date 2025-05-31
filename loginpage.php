<?php
session_start();

if(isset($_SESSION['user_id'])) {
  header("Location: homepage.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="styles/authentication.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400..900&family=Share+Tech+Mono&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <title>MyGameList</title>
  </head>
  <body>
    <div class="animation-container">
      <div class="gamepad">
        <div class="gamepad-top"></div>
        <div class="gamepad-body">
          <div class="d-pad">
            <div class="d-pad-center"></div>
            <div class="d-pad-up"></div>
            <div class="d-pad-right"></div>
            <div class="d-pad-down"></div>
            <div class="d-pad-left"></div>
          </div>
          <div class="buttons">
            <div class="button button-a"></div>
            <div class="button button-b"></div>
            <div class="button button-x"></div>
            <div class="button button-y"></div>
          </div>
          <div class="triggers">
            <div class="trigger trigger-left"></div>
            <div class="trigger trigger-right"></div>
          </div>
        </div>
      </div>
      <div class="pulse-effect"></div>
    </div>

    <div class="login-container">
      <h1>MyGameList</h1>
      <p class="subtitle">Enter the gaming universe</p>

      <form class="login-form" method="post" action="http://localhost/mygamelist/backend/signin.php">
        <?php
        if(isset($_SESSION['error'])) {
          echo '<div class="error-message">';
          echo '<p>' .  htmlspecialchars($_SESSION['error']) . '</p>';
          echo '</div>';
          unset($_SESSION['error']);
        }  
        $old_input = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);
        ?>
        <div class="input-group">
          <label for="username">Username or Email</label>
          <input
            type="text"
            id="username"
            placeholder="Enter your username or email"
            name="username"
            value="<?= htmlspecialchars($old_input["username"] ?? "") ?>"
          />
        </div>

        <div class="input-group">
          <label for="password">Password</label>
          <div class="password-input">
            <input
              type="password"
              id="password"
              placeholder="Enter your password"
              name="password"
            />
            <i class="fas fa-eye toggle-password" onclick="togglePassword(this)"></i>
          </div>
        </div>
        <div class="options">
          <div class="remember-me">
            <input type="checkbox" id="remember" name="remember" />
            <label for="remember">Remember me</label>
          </div>
          <a href="http://localhost/mygamelist/chpasspage.php" class="forgot-password">Forgot Password?</a>
        </div>

        <button type="submit" class="login-btn">Login</button>
        <div class="register-link">
          <p>Not a member? <a href="http://localhost/mygamelist/registerpage.php">Join now</a></p>
        </div>
      </form>
    </div>
    <script src="scripts/togglePasswordScript.js"></script>
  </body>
</html>
