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
    // De afspraak ophalen, met dienst, kenteken en klant
    $stmt = $conn->prepare("SELECT afspraken.*, diensten.naam AS dienst, autos.kenteken, users.naam AS klant
                            FROM afspraken
                            LEFT JOIN diensten ON diensten.dienst_id = afspraken.dienst_id
                            LEFT JOIN autos ON autos.auto_id = afspraken.auto_id
                            LEFT JOIN users ON users.user_id = autos.user_id
                            WHERE afspraken.afspraak_id = :id");
    $stmt->execute(['id' => $id]);
    $afspraak = $stmt->fetch(PDO::FETCH_ASSOC);

    // Alle monteurs ophalen voor de keuzelijst
    $stmt = $conn->prepare("SELECT users.user_id, users.naam
                            FROM users
                            JOIN rollen ON rollen.rol_id = users.rol_id
                            WHERE rollen.naam = 'monteur' AND users.deleted_at IS NULL
                            ORDER BY users.naam");
    $stmt->execute();
    $monteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

// Bestaat de afspraak niet, dan niet tonen
if (!$afspraak) {
    include '404.php';
    exit;
}

$titel = 'Afspraak afhandelen';
require 'header.php';
?>

<main class="container">
    <a href="beheer_afspraken_index.php" class="terug-link">&larr; Terug naar afspraken</a>

    <div class="paneel paneel-smal">
        <h1>Afspraak afhandelen</h1>

        <?php toonMelding(); ?>

        <dl class="kenmerken kenmerken-raster">
            <div><dt>Dienst</dt><dd><?= htmlspecialchars($afspraak['dienst'] ?? '') ?></dd></div>
            <div><dt>Klant</dt><dd><?= htmlspecialchars($afspraak['klant'] ?? '') ?></dd></div>
            <div><dt>Kenteken</dt><dd><span class="kenteken"><?= htmlspecialchars($afspraak['kenteken'] ?? '') ?></span></dd></div>
            <div><dt>Wanneer</dt><dd><?= htmlspecialchars(date('d-m-Y', strtotime($afspraak['datum']))) ?> om <?= htmlspecialchars(substr($afspraak['tijd'], 0, 5)) ?></dd></div>
        </dl>

        <form action="beheer_afspraken_process.php" method="post" class="formulier">
            <?php echo csrfVeld() ?>
            <input type="hidden" name="afspraak_id" value="<?= htmlspecialchars($afspraak['afspraak_id']) ?>">
            <div class="veld">
                <label for="monteur_id">Monteur</label>
                <select name="monteur_id" id="monteur_id">
                    <option value="">-- Nog geen monteur --</option>
                    <?php foreach ($monteurs as $monteur): ?>
                        <option value="<?= htmlspecialchars($monteur['user_id']) ?>" <?php if ($afspraak['monteur_id'] == $monteur['user_id']) echo 'selected' ?>>
                            <?= htmlspecialchars($monteur['naam']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="veld">
                <label for="kilometerstand">Kilometerstand <span class="optioneel">(na afloop)</span></label>
                <input type="number" name="kilometerstand" id="kilometerstand" min="0" value="<?= htmlspecialchars($afspraak['kilometerstand'] ?? '') ?>">
            </div>
            <div class="veld">
                <label for="opmerking">Opmerking <span class="optioneel">(na afloop)</span></label>
                <textarea name="opmerking" id="opmerking" placeholder="Bijvoorbeeld: remblokken bijna op"><?= htmlspecialchars($afspraak['opmerking'] ?? '') ?></textarea>
            </div>
            <button type="submit" name="submit" class="knop knop-breed">Opslaan</button>
        </form>
    </div>
</main>

<?php include 'footer.php'; ?>
