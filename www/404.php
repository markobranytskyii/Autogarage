<?php
require_once __DIR__ . '/auth.php';
http_response_code(404);
$isNotFoundPage = true;
$titel = 'Pagina niet gevonden';
require_once __DIR__ . '/header.php';
?>
<main class="container">
    <div class="foutpagina">
        <span class="foutcode">404</span>
        <h1>Pagina niet gevonden</h1>
        <p>De pagina die je zoekt bestaat niet (meer).</p>
        <p class="foutpagina-sub">Mogelijk is de link verouderd of is de pagina verwijderd.</p>
        <a href="index.php" class="knop">Terug naar home</a>
    </div>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
