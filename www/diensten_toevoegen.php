<?php
require 'auth.php';
require 'meldingen.php';
vereisRol('admin', 'medewerker');

$titel = 'Dienst toevoegen';
include 'header.php';
?>

<main class="container">
    <a href="diensten_index.php" class="terug-link">&larr; Terug naar diensten</a>

    <div class="paneel paneel-smal">
        <h1>Dienst toevoegen</h1>

        <?php toonMelding(); ?>

        <form action="diensten_toevoegen_process.php" method="post" class="formulier">
            <?php echo csrfVeld() ?>
            <div class="veld">
                <label for="naam">Naam</label>
                <input type="text" name="naam" id="naam" placeholder="APK-keuring" required>
            </div>
            <div class="veld">
                <label for="omschrijving">Omschrijving</label>
                <textarea name="omschrijving" id="omschrijving" placeholder="Wettelijke APK-keuring van je auto." required></textarea>
            </div>
            <div class="veld-rij">
                <div class="veld">
                    <label for="duur_minuten">Duur (minuten)</label>
                    <input type="number" name="duur_minuten" id="duur_minuten" min="1" placeholder="60" required>
                </div>
                <div class="veld">
                    <label for="prijs">Prijs (&euro;)</label>
                    <input type="number" name="prijs" id="prijs" step="0.01" min="0" placeholder="49.95" required>
                </div>
            </div>
            <div class="veld">
                <label for="images">Afbeelding <span class="optioneel">(pad of URL, optioneel)</span></label>
                <input type="text" name="images" id="images" placeholder="images/APK-keuring.jpg">
            </div>
            <button type="submit" name="submit" class="knop knop-breed">Dienst toevoegen</button>
        </form>
    </div>
</main>

<?php include 'footer.php'; ?>
