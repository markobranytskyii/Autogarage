<?php
require 'auth.php';
require 'meldingen.php';
vereisRol('klant');

$titel = 'Auto toevoegen';
include 'header.php';
?>

<main class="container">
    <a href="autos_index.php" class="terug-link">&larr; Terug naar mijn auto's</a>

    <div class="paneel paneel-smal">
        <h1>Auto toevoegen</h1>

        <?php toonMelding(); ?>

        <form action="autos_toevoegen_process.php" method="post" class="formulier">
            <?php echo csrfVeld() ?>
            <div class="veld">
                <label for="kenteken">Kenteken</label>
                <input type="text" name="kenteken" id="kenteken" placeholder="12-ABC-3" maxlength="10" required>
            </div>
            <div class="veld-rij">
                <div class="veld">
                    <label for="merk">Merk</label>
                    <input type="text" name="merk" id="merk" placeholder="Volkswagen" required>
                </div>
                <div class="veld">
                    <label for="model">Model</label>
                    <input type="text" name="model" id="model" placeholder="Golf" required>
                </div>
            </div>
            <div class="veld">
                <label for="bouwjaar">Bouwjaar</label>
                <input type="number" name="bouwjaar" id="bouwjaar" min="1900" max="<?php echo date('Y') + 1 ?>" placeholder="2015" required>
            </div>
            <button type="submit" name="submit" class="knop knop-breed">Auto toevoegen</button>
        </form>
    </div>
</main>

<?php include 'footer.php'; ?>
