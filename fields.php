<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$page_class = "fields-page";
require_once "includes/header.php";

// Marrim sportet direkt nga databaza
$result = $conn->query("SELECT * FROM sports");
?>

<div class="fields-page">
    <section class="fields-section">
        <?php while ($sport = $result->fetch_assoc()): ?>
            <a class="field-card"
                href="field-details.php?sport_id=<?= $sport['id'] ?>&name=<?= urlencode($sport['name']) ?>">
                <img src="/WebPage_Project/images/<?= htmlspecialchars($sport['img']) ?>"
                    alt="<?= htmlspecialchars($sport['name']) ?>">
                <div class="field-info">
                    <h3><?= htmlspecialchars($sport['name']) ?></h3>
                </div>
            </a>
        <?php endwhile; ?>
    </section>
</div>

<?php require_once "includes/footer.php"; ?>