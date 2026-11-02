<?php
require_once __DIR__ . '/auth.php';
http_response_code(500);
$titel = 'Serverfout';
require 'header.php';
?>
<main class="container">
    <div class="foutpagina">
        <span class="foutcode">500</span>
        <h1>Er ging iets mis</h1>
        <p>Er is iets fout gegaan op de server.</p>
        <p class="foutpagina-sub">Probeer het later opnieuw of neem contact op met de garage.</p>
        <a href="index.php" class="knop">Terug naar home</a>
    </div>
</main>
<?php require 'footer.php'; ?>
