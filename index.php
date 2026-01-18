<?php 
$page_class = "register-bg";
require_once "includes/session.php";
require_once "includes/header.php"; 
?>

<div class="hero-section">
    <div class="hero-content">
        <h1>MintPlayground</h1>
        
        <p>Unlock your game, book your field in seconds</p>

      <div class="nav-button">
          <?php if (!isset($_SESSION['user_id'])): ?>
             <a href="login.php" class="btn-book">Login to Reserve</a>
          <?php else: ?>
             <a href="fields.php" class="btn-book">Reserve Now</a>
          <?php endif; ?>
      </div>
    </div>
</div>


<?php require_once "includes/footer.php"; ?>
