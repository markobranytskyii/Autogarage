<?php
require 'auth.php';
require 'meldingen.php';
vereisRol('klant');
require 'database.php';

// id uit de URL moet een geldig getal zijn
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    include '404.php';
    exit;
}

$id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // De auto ophalen op auto_id, en alleen als hij van de ingelogde gebruiker is
    $stmt = $conn->prepare("SELECT * FROM autos WHERE auto_id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $id, 'user_id' => $user_id]);
    $auto = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

// Bestaat de auto niet (of is hij niet van jou), dan niet tonen
if (!$auto) {
    include '404.php';
    exit;
}

$titel = 'Auto bewerken';
require 'header.php';
?>

<main class="container">
    <a href="autos_index.php" class="terug-link">&larr; Terug naar mijn auto's</a>

    <div class="paneel paneel-smal">
        <h1>Auto bewerken</h1>

        <?php toonMelding(); ?>

        <form action="auto_edit_process.php" method="post" class="formulier">
            <?php echo csrfVeld() ?>
            <input type="hidden" name="auto_id" value="<?= htmlspecialchars($auto['auto_id']) ?>">
            <div class="veld">
                <label for="kenteken">Kenteken</label>
                <input type="text" name="kenteken" id="kenteken" maxlength="10" value="<?= htmlspecialchars($auto['kenteken']) ?>" required>
            </div>
            <div class="veld-rij">
                <div class="veld">
                    <label for="merk">Merk</label>
                    <input type="text" name="merk" id="merk" value="<?= htmlspecialchars($auto['merk']) ?>" required>
                </div>
                <div class="veld">
                    <label for="model">Model</label>
                    <input type="text" name="model" id="model" value="<?= htmlspecialchars($auto['model']) ?>" required>
                </div>
            </div>
            <div class="veld">
                <label for="bouwjaar">Bouwjaar</label>
                <input type="number" name="bouwjaar" id="bouwjaar" min="1900" max="<?php echo date('Y') + 1 ?>" value="<?= htmlspecialchars($auto['bouwjaar']) ?>" required>
            </div>
            <button type="submit" name="submit" class="knop knop-breed">Opslaan</button>
        </form>
    </div>
</main>

<?php include 'footer.php'; ?>
