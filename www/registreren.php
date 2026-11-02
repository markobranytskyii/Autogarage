<?php
require 'auth.php';
require 'meldingen.php';

$titel = 'Registreren';
include 'header.php';
?>

<main class="container">
    <div class="paneel paneel-smal">
        <h1>Account aanmaken</h1>
        <p class="paneel-intro">Met een account voeg je je auto's toe en plan je zelf een afspraak.</p>

        <?php toonMelding(); ?>

        <form action="user_register_add.php" method="post" class="formulier">
            <?php echo csrfVeld() ?>
            <div class="veld">
                <label for="naam">Naam</label>
                <input type="text" name="naam" id="naam" placeholder="Voor- en achternaam" autocomplete="name" required>
            </div>
            <div class="veld">
                <label for="email">E-mailadres</label>
                <input type="email" name="email" id="email" placeholder="naam@voorbeeld.nl" autocomplete="email" required>
            </div>
            <div class="veld">
                <label for="wachtwoord">Wachtwoord</label>
                <input type="password" name="wachtwoord" id="wachtwoord" placeholder="Minimaal 8 tekens" minlength="8" autocomplete="new-password" required>
            </div>
            <button type="submit" name="submit" class="knop knop-breed">Account aanmaken</button>
        </form>

        <p class="paneel-voet">Heb je al een account? <a href="login.php">Log hier in</a>.</p>
    </div>
</main>

<?php include 'footer.php'; ?>
