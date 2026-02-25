<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF'], ".php");
?>
<head>
  <link rel="stylesheet" href="CSS/navbar.css">
</head>
<nav>
  <div class="logo">
    <!-- SVG logo ili slika logotipa -->
    <a href="index.php"><img src="logo.png" alt="Ticketing System Logo" style="width:170px; height:auto; margin-right:20px;"></a>
  </div>
  <ul>
    <li><a href="#"></a></li>
    <li><a href="#"></a></li>
    <li><a href="#"></a></li>
    <?php if (isset($_SESSION["username"])): ?>
      <li class="nav-item">
        <span class="nav-link user-greet">Greetings, <?= htmlspecialchars($_SESSION["username"]); ?></span>
      </li>
      <?php if (isset($_SESSION["userrole"])): ?>
      <li class="nav-item">
        <span class="nav-link user-role">(<?= htmlspecialchars($_SESSION["userrole"]); ?>)</span>
      </li>
      <?php endif; ?>
      <li class="nav-item">
        <a class="nav-link" href="Backend/logout.inc.php">Log out</a>
      </li>
    <?php else: ?>
      <li class="nav-item">
        <a class="nav-link <?= ($current_page == 'login') ? 'active' : ''; ?>" href="login.php">
          <i class="fas fa-sign-in-alt"></i> Log in
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= ($current_page == 'signup') ? 'active' : ''; ?>" href="Registracija.php">
          <i class="fas fa-user-plus"></i> Register
        </a>
      </li>
    <?php endif; ?>
  </ul>
</nav>
