<?php
require 'auth.php';
require 'meldingen.php';
vereisRol('admin', 'medewerker');
require 'database.php';

// id uit de URL moet een geldig getal zijn
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    include '404.php';
    exit;
}

$id = $_GET['id'];

try {
    // De dienst ophalen die bewerkt wordt
    $stmt = $conn->prepare("SELECT * FROM diensten WHERE dienst_id = :id");
    $stmt->execute(['id' => $id]);
    $dienst = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

// Bestaat de dienst niet, dan 404
if (!$dienst) {
    include '404.php';
    exit;
}

$titel = 'Dienst bewerken';
require 'header.php';
?>

<main class="container">
    <a href="diensten_index.php" class="terug-link">&larr; Terug naar diensten</a>

    <div class="paneel paneel-smal">
        <h1>Dienst bewerken</h1>

        <?php toonMelding(); ?>

        <form action="diensten_bewerken_process.php" method="post" class="formulier">
            <?php echo csrfVeld() ?>
            <input type="hidden" name="dienst_id" value="<?= htmlspecialchars($dienst['dienst_id']) ?>">
            <div class="veld">
                <label for="naam">Naam</label>
                <input type="text" name="naam" id="naam" value="<?= htmlspecialchars($dienst['naam']) ?>" required>
            </div>
            <div class="veld">
                <label for="omschrijving">Omschrijving</label>
                <textarea name="omschrijving" id="omschrijving" required><?= htmlspecialchars($dienst['omschrijving']) ?></textarea>
            </div>
            <div class="veld-rij">
                <div class="veld">
                    <label for="duur_minuten">Duur (minuten)</label>
                    <input type="number" name="duur_minuten" id="duur_minuten" min="1" value="<?= htmlspecialchars($dienst['duur_minuten']) ?>" required>
                </div>
                <div class="veld">
                    <label for="prijs">Prijs (&euro;)</label>
                    <input type="number" name="prijs" id="prijs" step="0.01" min="0" value="<?= htmlspecialchars($dienst['prijs']) ?>" required>
                </div>
            </div>
            <button type="submit" name="submit" class="knop knop-breed">Opslaan</button>
        </form>
    </div>
</main>

<?php include 'footer.php'; ?>
