<?php
require 'auth.php';
require 'meldingen.php';
vereisRol('admin', 'medewerker');
require 'database.php';

try {
    // Keuzelijst monteurs ophalen uit de database (zonder dubbele waardes)
    $stmt = $conn->prepare("SELECT DISTINCT users.user_id, users.naam
                            FROM afspraken
                            JOIN users ON users.user_id = afspraken.monteur_id
                            ORDER BY users.naam");
    $stmt->execute();
    $monteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Keuzelijst diensten ophalen uit de database (zonder dubbele waardes)
    $stmt = $conn->prepare("SELECT DISTINCT diensten.dienst_id, diensten.naam
                            FROM afspraken
                            JOIN diensten ON diensten.dienst_id = afspraken.dienst_id
                            ORDER BY diensten.naam");
    $stmt->execute();
    $diensten = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Afspraken ophalen (filteren op monteur en/of dienst)
    $monteur = is_string($_POST['monteur'] ?? null) ? $_POST['monteur'] : '';
    $dienst = is_string($_POST['dienst'] ?? null) ? $_POST['dienst'] : '';

    $where = [];
    $params = [];

    if ($monteur === 'geen') {
        $where[] = 'afspraken.monteur_id IS NULL';
    } elseif ($monteur !== '' && is_numeric($monteur)) {
        $where[] = 'afspraken.monteur_id = :monteur';
        $params['monteur'] = $monteur;
    } else {
        $monteur = '';
    }

    if ($dienst !== '' && is_numeric($dienst)) {
        $where[] = 'afspraken.dienst_id = :dienst';
        $params['dienst'] = $dienst;
    } else {
        $dienst = '';
    }

    // LEFT JOIN: afspraken zonder monteur staan er ook in
    $sql = "SELECT afspraken.afspraak_id, afspraken.datum, afspraken.tijd,
                   diensten.naam AS dienst,
                   autos.kenteken,
                   klant.naam AS klant,
                   monteur.naam AS monteur
            FROM afspraken
            LEFT JOIN diensten ON diensten.dienst_id = afspraken.dienst_id
            LEFT JOIN autos ON autos.auto_id = afspraken.auto_id
            LEFT JOIN users AS klant ON klant.user_id = autos.user_id
            LEFT JOIN users AS monteur ON monteur.user_id = afspraken.monteur_id";
    if (count($where) > 0) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }
    $sql .= " ORDER BY afspraken.datum, afspraken.tijd";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $afspraken = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log de error (niet tonen aan gebruiker!)
    error_log("Database error: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

$titel = 'Afspraken beheren';
include 'header.php';
?>

<main class="container">
    <div class="pagina-kop">
        <div>
            <h1>Afspraken beheren</h1>
            <p>Wijs monteurs toe en noteer na afloop de kilometerstand.</p>
        </div>
        <span class="teller"><?php echo count($afspraken) ?> afspraken</span>
    </div>

    <?php toonMelding(); ?>

    <form method="POST" class="filterbalk">
        <div class="filter-veld">
            <label for="monteur">Monteur</label>
            <select name="monteur" id="monteur">
                <option value="">Alle monteurs</option>
                <option value="geen" <?php if ($monteur == 'geen') echo 'selected' ?>>Zonder monteur</option>
                <?php foreach ($monteurs as $m): ?>
                    <option value="<?php echo htmlspecialchars($m['user_id']) ?>" <?php if ($monteur == $m['user_id']) echo 'selected' ?>>
                        <?php echo htmlspecialchars($m['naam']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-veld">
            <label for="dienst">Dienst</label>
            <select name="dienst" id="dienst">
                <option value="">Alle diensten</option>
                <?php foreach ($diensten as $d): ?>
                    <option value="<?php echo htmlspecialchars($d['dienst_id']) ?>" <?php if ($dienst == $d['dienst_id']) echo 'selected' ?>>
                        <?php echo htmlspecialchars($d['naam']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="knop">Filteren</button>
    </form>

    <div class="paneel">
        <?php if (count($afspraken) == 0): ?>
            <p class="leeg">Er zijn geen afspraken die bij je keuze passen.</p>
        <?php else: ?>
            <div class="tabel-wrap">
                <table class="tabel">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Tijd</th>
                            <th>Klant</th>
                            <th>Kenteken</th>
                            <th>Dienst</th>
                            <th>Monteur</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($afspraken as $afspraak): ?>
                            <tr>
                                <!-- htmlspecialchars op elk veld: XSS voorkomen -->
                                <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($afspraak['datum']))) ?></td>
                                <td><?php echo htmlspecialchars(substr($afspraak['tijd'], 0, 5)) ?></td>
                                <td><?php echo htmlspecialchars($afspraak['klant'] ?? '') ?></td>
                                <td><span class="kenteken"><?php echo htmlspecialchars($afspraak['kenteken'] ?? '') ?></span></td>
                                <td><?php echo htmlspecialchars($afspraak['dienst'] ?? '') ?></td>
                                <td>
                                    <?php if ($afspraak['monteur']): ?>
                                        <?php echo htmlspecialchars($afspraak['monteur']) ?>
                                    <?php else: ?>
                                        <span class="status status-open">Nog geen monteur</span>
                                    <?php endif; ?>
                                </td>
                                <td class="rechts">
                                    <a href="beheer_afspraken_bewerken.php?id=<?php echo htmlspecialchars($afspraak['afspraak_id']) ?>" class="tabel-link">Afhandelen</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>
