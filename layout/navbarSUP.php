<?php
if(!isset($_SESSION)) { session_start(); }

$current_page = basename($_SERVER['PHP_SELF'], ".php");
?>

<head>
  <link rel="stylesheet" href="CSS/navbar.css">
  <style>

   .search{
      
      border-color: #4A90E2;
      background-color: #121212;
      padding: 30%;
      height: 30px;
      width: 200px;
      color: white;
      font-size: 100px;

   }
.search::placeholder{
     color: #a3bbf7;
      
   }
   
  </style>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
</head>
<nav>
  <div class="logo">
    
    <!-- SVG logo ili slika logotipa -->
    <a href="../supervisor/index-sup.php"><img src="../logo.png" alt="Ticketing System Logo" style="width:170px; height:auto; margin-right:20px;"></a>
    <div class="kontejnercic">

    
    <form action="ticket-details.php" method="GET" style="display:inline-block; margin-left:20px;">
  <input type="number" class="search" name="id" placeholder="Search Case" required min="1" style="padding:5px; font-size:14px;">
  
</form>

</div>
  </div>

  <ul>
    

    <li class="nav-item">
      <a class="nav-link" href="../supervisor/index-sup.php">Dashboard</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="../supervisor/Users.php">Users</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="../supervisor/CreateTicket.php">Create ticket</a>
    </li>

  </ul>


  <ul>

    <?php if (isset($_SESSION["username"])): ?>
      <li class="nav-item user-menu">
  <button class="user-menu-btn">
    <span class="user-icon">
      <!-- Ikonica korisnika (Font Awesome ili SVG) -->
      <i class="fas fa-user-circle"></i>
    </span>
    <span class="user-name"><?= htmlspecialchars($_SESSION["username"]); ?></span>
    <span class="user-role">(<?= htmlspecialchars($_SESSION["userrole"] ?? ''); ?>)</span>
    <svg width="16" height="16" style="vertical-align:middle;"><path fill="currentColor" d="M4 6l4 4 4-4"/></svg>
  </button>
  <div class="user-dropdown">
    <a href="profile.php" class="dropdown-link">Profile</a>
    <a href="../Backend/logout.inc.php" class="dropdown-link">Log out</a>
  </div>
</li>

    <?php else: ?>
      <li class="nav-item">
        <a class="nav-link <?= ($current_page == 'login') ? 'active' : ''; ?>" href="../login.php">
          <i class="fas fa-sign-in-alt"></i> Log in
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= ($current_page == 'signup') ? 'active' : ''; ?>" href="../Registracija.php">
          <i class="fas fa-user-plus"></i> Register
        </a>
      </li>
    <?php endif; ?>
  </ul>
</nav>
<script>
document.querySelectorAll('.user-menu-btn').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.stopPropagation();
    document.querySelectorAll('.user-dropdown').forEach(d => d.style.display = 'none');
    btn.nextElementSibling.style.display = 'block';
    btn.classList.add('active');
  });
});
document.body.addEventListener('click', function() {
  document.querySelectorAll('.user-dropdown').forEach(d => d.style.display = 'none');
  document.querySelectorAll('.user-menu-btn').forEach(btn => btn.classList.remove('active'));
});
</script>
<script>
  const clearInput = () => {
  const input = document.getElementsByTagName("input")[0];
  input.value = "";
}
</script>
