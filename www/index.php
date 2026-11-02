<?php
require 'auth.php';
require 'database.php';

try {
    // Keuzelijst duur ophalen uit de database (zonder dubbele waardes)
    $stmt = $conn->prepare("SELECT DISTINCT duur_minuten FROM diensten WHERE deleted_at IS NULL ORDER BY duur_minuten");
    $stmt->execute();
    $duren = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Diensten ophalen (filteren op duur en/of prijs, sorteren op prijs of duur)
    $duur = is_string($_POST['duur'] ?? null) ? $_POST['duur'] : '';
    $prijs = is_string($_POST['prijs'] ?? null) ? $_POST['prijs'] : '';
    $sorteer = is_string($_POST['sorteer'] ?? null) ? $_POST['sorteer'] : '';

    $where = ['diensten.deleted_at IS NULL'];
    $params = [];

    if ($duur !== '' && is_numeric($duur)) {
        $where[] = 'diensten.duur_minuten = :duur';
        $params['duur'] = $duur;
    }

    if ($prijs === 'goedkoop') {
        $where[] = 'diensten.prijs < 50';
    } elseif ($prijs === 'gemiddeld') {
        $where[] = 'diensten.prijs BETWEEN 50 AND 150';
    } elseif ($prijs === 'duur') {
        $where[] = 'diensten.prijs > 150';
    } else {
        $prijs = '';
    }

    $sorteringen = [
        'prijs_op' => 'diensten.prijs ASC',
        'prijs_af' => 'diensten.prijs DESC',
        'duur_op' => 'diensten.duur_minuten ASC',
        'duur_af' => 'diensten.duur_minuten DESC',
    ];
    $order = $sorteringen[$sorteer] ?? 'diensten.prijs ASC';
    if (!isset($sorteringen[$sorteer])) {
        $sorteer = '';
    }

    $sql = "SELECT diensten.*, COUNT(afspraken.afspraak_id) AS aantal_geboekt
            FROM diensten
            LEFT JOIN afspraken ON afspraken.dienst_id = diensten.dienst_id
            WHERE " . implode(' AND ', $where) . "
            GROUP BY diensten.dienst_id
            ORDER BY $order";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $diensten = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Log de error (niet tonen aan gebruiker!)
    error_log("Database error: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

$titel = 'APK, onderhoud en banden';
include 'header.php';
?>

<section class="hero">
    <div class="container hero-inhoud">
        <div class="hero-tekst">
            <span class="hero-label">Garage Sleutelwerk</span>
            <h1>Onderhoud aan je auto, <span>zonder gedoe.</span></h1>
            <p>APK, kleine en grote beurten, airco-service en banden wisselen. Kies zelf een dienst, datum en tijd: zo raakt er nooit meer een afspraak kwijt.</p>
            <div class="hero-knoppen">
                <a href="#diensten" class="knop knop-groot">Bekijk diensten</a>
                <?php if (!isIngelogd()): ?>
                    <a href="registreren.php" class="knop knop-groot knop-licht">Account aanmaken</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="hero-afbeelding">
            <img src="images/Grote-beurt.jpg" alt="Monteur aan het werk bij Garage Sleutelwerk">
        </div>
    </div>
</section>

<main class="container">

    <section class="usp-rij">
        <div class="usp">
            <span class="usp-nummer">01</span>
            <h3>Online afspraak</h3>
            <p>Kies je auto, een dienst en een vrij tijdslot. Klaar in een minuut.</p>
        </div>
        <div class="usp">
            <span class="usp-nummer">02</span>
            <h3>Vaste prijzen</h3>
            <p>De prijs bij het boeken is de prijs die je betaalt. Geen verrassingen achteraf.</p>
        </div>
        <div class="usp">
            <span class="usp-nummer">03</span>
            <h3>Ervaren monteurs</h3>
            <p>Na elke beurt noteren we de kilometerstand en laten we weten wat ons opviel.</p>
        </div>
    </section>

    <section class="over-ons" id="over-ons">
        <div class="over-ons-tekst">
            <h2>Over ons</h2>
            <p>
                Welkom bij Garage Sleutelwerk! Wij zijn een betrouwbare garage waar je terecht kunt voor
                APK-keuringen, kleine en grote beurten, airco-service en het wisselen van banden.
            </p>
            <p>
                Onze ervaren monteurs werken met zorg aan jouw auto, of het nu gaat om een snelle bandenwissel
                of een complete grote beurt. Na elke afspraak noteren we de kilometerstand en laten we je weten
                als ons iets opvalt, zodat je nooit voor verrassingen komt te staan.
            </p>
            <p>
                Een afspraak maken is eenvoudig: maak een account aan, voeg je auto's toe en kies zelf een dienst,
                datum en tijd die jou uitkomt. Zo hoef je niet meer te bellen en raakt er geen afspraak kwijt.
            </p>
        </div>
        <div class="over-ons-afbeelding">
            <img src="images/Kleine-beurt.jpg" alt="Onderhoud aan een auto">
        </div>
    </section>

    <section id="diensten">
        <div class="sectie-kop">
            <h2>Onze diensten</h2>
            <p>Filter op duur of prijs en kies de dienst die bij je past.</p>
        </div>

        <form method="POST" action="index.php#diensten" class="filterbalk">
            <div class="filter-veld">
                <label for="duur">Duur</label>
                <select name="duur" id="duur">
                    <option value="">Alle duren</option>
                    <?php foreach ($duren as $minuten): ?>
                        <option value="<?php echo htmlspecialchars($minuten) ?>" <?php if ($duur == $minuten) echo 'selected' ?>>
                            <?php echo htmlspecialchars($minuten) ?> minuten
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-veld">
                <label for="prijs">Prijs</label>
                <select name="prijs" id="prijs">
                    <option value="">Alle prijzen</option>
                    <option value="goedkoop" <?php if ($prijs == 'goedkoop') echo 'selected' ?>>Goedkoop (&lt; &euro;50)</option>
                    <option value="gemiddeld" <?php if ($prijs == 'gemiddeld') echo 'selected' ?>>Gemiddeld (&euro;50 - &euro;150)</option>
                    <option value="duur" <?php if ($prijs == 'duur') echo 'selected' ?>>Duur (&gt; &euro;150)</option>
                </select>
            </div>

            <div class="filter-veld">
                <label for="sorteer">Sorteren</label>
                <select name="sorteer" id="sorteer">
                    <option value="prijs_op" <?php if ($sorteer == 'prijs_op') echo 'selected' ?>>Prijs laag - hoog</option>
                    <option value="prijs_af" <?php if ($sorteer == 'prijs_af') echo 'selected' ?>>Prijs hoog - laag</option>
                    <option value="duur_op" <?php if ($sorteer == 'duur_op') echo 'selected' ?>>Duur kort - lang</option>
                    <option value="duur_af" <?php if ($sorteer == 'duur_af') echo 'selected' ?>>Duur lang - kort</option>
                </select>
            </div>

            <button type="submit" class="knop">Toon diensten</button>
        </form>

        <?php if (count($diensten) == 0): ?>
            <p class="leeg">Er zijn geen diensten die bij je keuze passen.</p>
        <?php endif; ?>

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
                        <div class="dienst-voet">
                            <span class="prijs">&euro; <?php echo htmlspecialchars($dienst['prijs']) ?></span>
                            <a href="diensten_detail.php?id=<?php echo htmlspecialchars($dienst['dienst_id']) ?>" class="knop knop-klein">Details</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

</main>

<?php include 'footer.php'; ?>
