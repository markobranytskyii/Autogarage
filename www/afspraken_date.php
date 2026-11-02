<?php
require 'auth.php';
vereisRol('klant');
require 'database.php';

// dienst_id uit de URL moet een geldig getal zijn
if (!isset($_GET['dienst_id']) || !is_numeric($_GET['dienst_id'])) {
    include '404.php';
    exit;
}

$user_id = $_SESSION['user_id'];
$dienst_id = $_GET['dienst_id'];

try {
    // De gekozen dienst ophalen (voor naam en prijs)
    $stmt = $conn->prepare("SELECT * FROM diensten WHERE dienst_id = :dienst_id AND deleted_at IS NULL");
    $stmt->execute(['dienst_id' => $dienst_id]);
    $dienst = $stmt->fetch(PDO::FETCH_ASSOC);

    // De eigen auto's van de ingelogde gebruiker
    $stmt = $conn->prepare("SELECT * FROM autos WHERE user_id = :user_id AND deleted_at IS NULL ORDER BY merk, model");
    $stmt->execute(['user_id' => $user_id]);
    $autos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

if (!$dienst) {
    include '404.php';
    exit;
}

$tijden = ['10:00', '13:00', '16:00'];

// We beginnen op maandag van deze week
$maandag = strtotime('monday this week');

// De 7 dagen één voor één
$week = [
    ['dagnaam' => 'Maandag',   'datum' => date('Y-m-d', strtotime('+0 day', $maandag)), 'kort' => date('d-m', strtotime('+0 day', $maandag))],
    ['dagnaam' => 'Dinsdag',   'datum' => date('Y-m-d', strtotime('+1 day', $maandag)), 'kort' => date('d-m', strtotime('+1 day', $maandag))],
    ['dagnaam' => 'Woensdag',  'datum' => date('Y-m-d', strtotime('+2 day', $maandag)), 'kort' => date('d-m', strtotime('+2 day', $maandag))],
    ['dagnaam' => 'Donderdag', 'datum' => date('Y-m-d', strtotime('+3 day', $maandag)), 'kort' => date('d-m', strtotime('+3 day', $maandag))],
    ['dagnaam' => 'Vrijdag',   'datum' => date('Y-m-d', strtotime('+4 day', $maandag)), 'kort' => date('d-m', strtotime('+4 day', $maandag))],
    ['dagnaam' => 'Zaterdag',  'datum' => date('Y-m-d', strtotime('+5 day', $maandag)), 'kort' => date('d-m', strtotime('+5 day', $maandag))],
    ['dagnaam' => 'Zondag',    'datum' => date('Y-m-d', strtotime('+6 day', $maandag)), 'kort' => date('d-m', strtotime('+6 day', $maandag))],
];

$vandaag = date('Y-m-d');

$titel = 'Afspraak maken';

require 'header.php';
?>

<main class="container">
    <a href="diensten_detail.php?id=<?php echo htmlspecialchars($dienst['dienst_id']) ?>" class="terug-link">&larr; Terug naar dienst</a>

    <div class="paneel">
        <div class="pagina-kop pagina-kop-in-paneel">
            <div>
                <h1>Afspraak maken</h1>
                <p><?php echo htmlspecialchars($dienst['naam']) ?> &middot; <?php echo htmlspecialchars($dienst['duur_minuten']) ?> minuten</p>
            </div>
            <span class="prijs prijs-groot">&euro; <?php echo htmlspecialchars($dienst['prijs']) ?></span>
        </div>

        <?php if (count($autos) == 0): ?>
            <p class="leeg">Je hebt nog geen auto's. Voeg eerst een auto toe.</p>
            <a href="autos_toevoegen.php" class="knop">Auto toevoegen</a>
        <?php else: ?>

            <div class="stap">
                <span class="stap-nummer">1</span>
                <div class="veld">
                    <label for="auto">Kies je auto</label>
                    <select name="auto" id="auto">
                        <?php foreach ($autos as $auto): ?>
                            <option value="<?php echo htmlspecialchars($auto['auto_id']) ?>">
                                <?php echo htmlspecialchars($auto['kenteken'] . ' - ' . $auto['merk'] . ' ' . $auto['model']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="stap">
                <span class="stap-nummer">2</span>
                <p class="stap-titel">Kies een dag en tijd</p>
            </div>
            <div class="week">
                <?php foreach ($week as $dag): ?>
                    <div class="dag">
                        <h4><?php echo htmlspecialchars($dag['dagnaam']) ?><span><?php echo htmlspecialchars($dag['kort']) ?></span></h4>
                        <?php foreach ($tijden as $tijd): ?>
                            <button type="button" class="slot"
                                    data-datum="<?php echo htmlspecialchars($dag['datum']) ?>"
                                    data-tijd="<?php echo htmlspecialchars($tijd) ?>"
                                    <?php if ($dag['datum'] < $vandaag) echo 'disabled' ?>>
                                <?php echo htmlspecialchars($tijd) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="afspraak-voet">
                <p id="keuze" class="keuze">Nog geen tijd gekozen.</p>
                <button type="button" id="afspraakBtn" class="knop knop-groot">Afspraak bevestigen</button>
            </div>

        <?php endif; ?>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const afspraakBtn = document.querySelector('#afspraakBtn');
        const countSpan = document.querySelector('.afspraak-count');
        const slots = document.querySelectorAll('.slot');
        const autoSelect = document.querySelector('#auto');
        const keuze = document.querySelector('#keuze');

        let gekozenDatum = null;
        let gekozenTijd = null;

        // Een tijdslot kiezen: markeer de knop en onthoud datum + tijd
        slots.forEach(function (slot) {
            slot.addEventListener('click', function () {
                slots.forEach(s => s.classList.remove('gekozen'));
                slot.classList.add('gekozen');
                gekozenDatum = slot.dataset.datum;
                gekozenTijd = slot.dataset.tijd;
                keuze.className = 'keuze';
                keuze.textContent = 'Gekozen: ' + gekozenDatum + ' om ' + gekozenTijd;
            });
        });

        if (afspraakBtn && countSpan) {
            afspraakBtn.addEventListener('click', function (e) {
                e.preventDefault();

                if (!gekozenDatum || !gekozenTijd) {
                    keuze.className = 'keuze keuze-fout';
                    keuze.textContent = 'Kies eerst een dag en tijd.';
                    return;
                }

                // Teller in de navbar meteen +1 (zonder de pagina te herladen)
                let count = parseInt(countSpan.textContent, 10);
                countSpan.textContent = count + 1;

                // AJAX call naar de server maken (met CSRF-token in de header)
                fetch('afspraak_add.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?php echo htmlspecialchars(csrfToken()) ?>'
                    },
                    body: JSON.stringify({
                        auto_id: autoSelect.value,
                        dienst_id: <?php echo (int) $dienst['dienst_id']; ?>,
                        datum: gekozenDatum,
                        tijd: gekozenTijd
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        keuze.className = 'keuze keuze-succes';
                        keuze.textContent = 'Afspraak gemaakt! Bekijk hem bij "Mijn afspraken".';
                    } else {
                        countSpan.textContent = count; // teller terugzetten bij fout
                        keuze.className = 'keuze keuze-fout';
                        keuze.textContent = 'Er is iets misgegaan bij het maken van de afspraak.';
                    }
                })
                .catch(error => {
                    countSpan.textContent = count;
                    keuze.className = 'keuze keuze-fout';
                    keuze.textContent = 'Er is iets misgegaan. Probeer het opnieuw.';
                    console.log('Serverfout: ' + error);
                });
            });
        }
    });
</script>

<?php require 'footer.php' ?>
