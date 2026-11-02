<?php
require 'auth.php';
require 'meldingen.php';

$titel = 'Inloggen';
include 'header.php';
?>

<main class="container">
    <div class="paneel paneel-smal">
        <h1>Inloggen</h1>
        <p class="paneel-intro">Log in om je auto's en afspraken te beheren.</p>

        <?php toonMelding(); ?>

        <form action="login_process.php" method="post" class="formulier">
            <?php echo csrfVeld() ?>
            <div class="veld">
                <label for="email">E-mailadres</label>
                <input type="email" name="email" id="email" placeholder="naam@voorbeeld.nl" autocomplete="email" required>
            </div>
            <div class="veld">
                <label for="wachtwoord">Wachtwoord</label>
                <input type="password" name="wachtwoord" id="wachtwoord" placeholder="Je wachtwoord" autocomplete="current-password" required>
            </div>
            <button type="submit" name="submit" class="knop knop-breed">Inloggen</button>
        </form>

        <p class="paneel-voet">Nog geen account? <a href="registreren.php">Registreer je hier</a>.</p>
    </div>
</main>

<?php include 'footer.php'; ?>
