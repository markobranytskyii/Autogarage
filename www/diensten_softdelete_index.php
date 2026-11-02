<?php
require 'auth.php';
require 'meldingen.php';
vereisRol('admin', 'medewerker');
require 'database.php';

try {
    // Uitgezette diensten ophalen, inclusief het aantal afspraken per dienst.
    $stmt = $conn->prepare("SELECT diensten.*, COUNT(afspraken.afspraak_id) AS aantal_geboekt
                            FROM diensten
                            LEFT JOIN afspraken ON afspraken.dienst_id = diensten.dienst_id
                            WHERE diensten.deleted_at IS NOT NULL
                            GROUP BY diensten.dienst_id
                            ORDER BY diensten.prijs ASC");
    $stmt->execute();
    $diensten = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log de error (niet tonen aan gebruiker!)
    error_log("Database error: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

$titel = 'Prullenbak';
include 'header.php';
?>

<main class="container">
    <div class="pagina-kop">
        <div>
            <h1>Prullenbak</h1>
            <p>Uitgezette diensten. Zet ze terug of verwijder ze definitief.</p>
        </div>
    </div>

    <?php toonMelding(); ?>

    <?php if (count($diensten) == 0): ?>
        <p class="leeg">De prullenbak is leeg.</p>
    <?php endif; ?>

    <div class="diensten">
        <?php foreach ($diensten as $dienst): ?>
            <article class="dienst dienst-uit">
                <div class="dienst-afbeelding">
                    <img src="<?php echo htmlspecialchars($dienst['images'] ?? 'images/Kleine-beurt.jpg') ?>" alt="<?php echo htmlspecialchars($dienst['naam']) ?>">
                    <span class="badge">Uitgezet</span>
                </div>
                <div class="dienst-inhoud">
                    <h3><?php echo htmlspecialchars($dienst['naam']) ?></h3>
                    <p class="dienst-meta"><?php echo htmlspecialchars($dienst['aantal_geboekt']) ?> keer geboekt</p>
                    <span class="prijs">&euro; <?php echo htmlspecialchars($dienst['prijs']) ?></span>
                    <div class="knoppen-rij">
                        <form action="diensten_terugzetten.php" method="post" class="inline-form">
                            <?php echo csrfVeld() ?>
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($dienst['dienst_id']) ?>">
                            <button type="submit" class="knop knop-klein knop-secundair">Terugzetten</button>
                        </form>
                        <form action="diensten_delete.php" method="post" class="inline-form"
                              onsubmit="return confirm('Weet je het zeker? De dienst en alle afspraken ervan worden definitief verwijderd.');">
                            <?php echo csrfVeld() ?>
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($dienst['dienst_id']) ?>">
                            <button type="submit" class="knop knop-klein knop-gevaar">Definitief verwijderen</button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</main>

<?php include 'footer.php'; ?>
