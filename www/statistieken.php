<?php
require 'auth.php';
vereisRol('admin');
require 'database.php';

try {
    // Aantal afspraken en omzet per dienst (COUNT, SUM, GROUP BY)
    // LEFT JOIN: diensten zonder afspraken staan er ook in
    $stmt = $conn->prepare("SELECT diensten.naam,
                                   COUNT(afspraken.afspraak_id) AS aantal_afspraken,
                                   SUM(afspraken.prijs) AS omzet
                            FROM diensten
                            LEFT JOIN afspraken ON afspraken.dienst_id = diensten.dienst_id
                            GROUP BY diensten.dienst_id, diensten.naam
                            ORDER BY aantal_afspraken DESC");
    $stmt->execute();
    $per_dienst = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Gemiddelde prijs per afspraak (AVG)
    $stmt = $conn->prepare("SELECT ROUND(AVG(prijs), 2) FROM afspraken");
    $stmt->execute();
    $gemiddelde_prijs = $stmt->fetchColumn();

    // Oudste en nieuwste bouwjaar (MIN, MAX)
    $stmt = $conn->prepare("SELECT MIN(bouwjaar) AS oudste, MAX(bouwjaar) AS nieuwste
                            FROM autos
                            WHERE deleted_at IS NULL");
    $stmt->execute();
    $bouwjaren = $stmt->fetch(PDO::FETCH_ASSOC);

    // Merken waarvan we meer dan twee auto's hebben (COUNT, GROUP BY, HAVING)
    $stmt = $conn->prepare("SELECT merk, COUNT(*) AS aantal
                            FROM autos
                            WHERE deleted_at IS NULL
                            GROUP BY merk
                            HAVING COUNT(*) > 2
                            ORDER BY aantal DESC");
    $stmt->execute();
    $merken = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log de error (niet tonen aan gebruiker!)
    error_log("Database error: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

// Totalen voor de cijferkaarten bovenaan (uit de resultaten hierboven)
$totaal_afspraken = array_sum(array_column($per_dienst, 'aantal_afspraken'));
$totale_omzet = array_sum(array_column($per_dienst, 'omzet'));
$hoogste_aantal = max(array_merge([1], array_column($per_dienst, 'aantal_afspraken')));

$titel = 'Statistieken';
include 'header.php';
?>

<main class="container">
    <div class="pagina-kop">
        <div>
            <h1>Statistieken</h1>
            <p>De belangrijkste cijfers van Garage Sleutelwerk in één overzicht.</p>
        </div>
    </div>

    <section class="cijfers">
        <div class="cijfer">
            <span class="cijfer-label">Afspraken</span>
            <strong><?php echo htmlspecialchars($totaal_afspraken) ?></strong>
        </div>
        <div class="cijfer">
            <span class="cijfer-label">Totale omzet</span>
            <strong>&euro; <?php echo htmlspecialchars(number_format($totale_omzet, 2, ',', '.')) ?></strong>
        </div>
        <div class="cijfer">
            <span class="cijfer-label">Gem. prijs per afspraak</span>
            <strong>&euro; <?php echo htmlspecialchars(number_format((float) ($gemiddelde_prijs ?? 0), 2, ',', '.')) ?></strong>
        </div>
        <div class="cijfer">
            <span class="cijfer-label">Bouwjaren</span>
            <strong><?php echo htmlspecialchars($bouwjaren['oudste'] ?? '-') ?> - <?php echo htmlspecialchars($bouwjaren['nieuwste'] ?? '-') ?></strong>
            <span class="cijfer-sub">oudste en nieuwste auto</span>
        </div>
    </section>

    <div class="raster-2">
        <div class="paneel">
            <h2>Afspraken en omzet per dienst</h2>
            <?php if (count($per_dienst) == 0): ?>
                <p class="leeg">Er zijn nog geen diensten.</p>
            <?php else: ?>
                <div class="tabel-wrap">
                    <table class="tabel">
                        <thead>
                            <tr>
                                <th>Dienst</th>
                                <th>Afspraken</th>
                                <th class="rechts">Omzet</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($per_dienst as $dienst): ?>
                                <tr>
                                    <!-- htmlspecialchars op elk veld: XSS voorkomen -->
                                    <td><?php echo htmlspecialchars($dienst['naam']) ?></td>
                                    <td>
                                        <div class="balk-cel">
                                            <span class="balk" style="width: <?php echo round($dienst['aantal_afspraken'] / $hoogste_aantal * 100) ?>%"></span>
                                            <span><?php echo htmlspecialchars($dienst['aantal_afspraken']) ?></span>
                                        </div>
                                    </td>
                                    <td class="rechts nowrap">&euro; <?php echo htmlspecialchars(number_format((float) ($dienst['omzet'] ?? 0), 2, ',', '.')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="paneel">
            <h2>Merken met meer dan twee auto's</h2>
            <?php if (count($merken) == 0): ?>
                <p class="leeg">Er is geen merk met meer dan twee auto's.</p>
            <?php else: ?>
                <div class="tabel-wrap">
                    <table class="tabel">
                        <thead>
                            <tr>
                                <th>Merk</th>
                                <th class="rechts">Aantal auto's</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($merken as $merk): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($merk['merk']) ?></td>
                                    <td class="rechts"><?php echo htmlspecialchars($merk['aantal']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
