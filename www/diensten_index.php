<?php
require 'auth.php';
require 'meldingen.php';
vereisRol('admin', 'medewerker');
require 'database.php';

try {
    // Diensten ophalen, inclusief het aantal afspraken per dienst.
    $stmt = $conn->prepare("SELECT diensten.*, COUNT(afspraken.afspraak_id) AS aantal_geboekt
                            FROM diensten
                            LEFT JOIN afspraken ON afspraken.dienst_id = diensten.dienst_id
                            WHERE diensten.deleted_at IS NULL
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

$titel = 'Diensten beheren';
include 'header.php';
?>

<main class="container">
    <div class="pagina-kop">
        <div>
            <h1>Diensten beheren</h1>
            <p>Voeg diensten toe, pas prijzen aan of zet diensten uit.</p>
        </div>
        <a href="diensten_toevoegen.php" class="knop">+ Dienst toevoegen</a>
    </div>

    <?php toonMelding(); ?>

    <div class="diensten">
        <?php foreach ($diensten as $dienst): ?>
            <article class="dienst">
                <div class="dienst-afbeelding">
                    <img src="<?php echo htmlspecialchars($dienst['images'] ?? 'images/Kleine-beurt.jpg') ?>" alt="<?php echo htmlspecialchars($dienst['naam']) ?>">
                    <span class="badge"><?php echo htmlspecialchars($dienst['duur_minuten']) ?> min</span>
                </div>
                <div class="dienst-inhoud">
                    <h3><?php echo htmlspecialchars($dienst['naam']) ?></h3>
                    <p class="dienst-meta"><?php echo htmlspecialchars($dienst['aantal_geboekt']) ?> keer geboekt</p>
                    <span class="prijs">&euro; <?php echo htmlspecialchars($dienst['prijs']) ?></span>
                    <div class="knoppen-rij">
                        <a href="diensten_bewerken.php?id=<?php echo htmlspecialchars($dienst['dienst_id']) ?>" class="knop knop-klein knop-secundair">Bewerken</a>
                        <!-- Verwijderen gaat via POST, niet via een link -->
                        <form action="diensten_softdelete.php" method="post" class="inline-form">
                            <?php echo csrfVeld() ?>
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($dienst['dienst_id']) ?>">
                            <button type="submit" class="knop knop-klein knop-gevaar">Uitzetten</button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</main>

<?php include 'footer.php'; ?>
