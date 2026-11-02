<?php
require_once __DIR__ . '/auth.php';
http_response_code(403);
$titel = 'Geen toegang';
require 'header.php';
?>
<main class="container">
    <div class="foutpagina">
        <span class="foutcode">403</span>
        <h1>Geen toegang</h1>
        <p>Je hebt geen toestemming om deze pagina te bekijken.</p>
        <?php if (isIngelogd()): ?>
            <p class="foutpagina-sub">Je bent ingelogd als <strong><?php echo htmlspecialchars($_SESSION['email'] ?? 'gebruiker') ?></strong>, maar je hebt niet de juiste rechten.</p>
            <a href="index.php" class="knop">Terug naar home</a>
        <?php else: ?>
            <p class="foutpagina-sub">Je bent niet ingelogd.</p>
            <a href="login.php" class="knop">Inloggen</a>
        <?php endif; ?>
    </div>
</main>
<?php require 'footer.php'; ?>
