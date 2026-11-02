<?php
require 'auth.php';
require 'meldingen.php';
vereisRol('admin', 'medewerker');
vereisPost();
controleerCsrf();

// afspraak_id is verplicht en moet een getal zijn
if (empty($_POST['afspraak_id']) || !is_numeric($_POST['afspraak_id'])) {
    stuurTerug('beheer_afspraken_index.php', 'ongeldig');
}

$afspraak_id = $_POST['afspraak_id'];
$terug = 'beheer_afspraken_bewerken.php?id=' . $afspraak_id;

require 'database.php';

// Monteur, kilometerstand en opmerking mogen leeg zijn: leeg = NULL in de database
$monteur_id = !empty($_POST['monteur_id']) ? $_POST['monteur_id'] : null;
$kilometerstand = (isset($_POST['kilometerstand']) && $_POST['kilometerstand'] !== '') ? $_POST['kilometerstand'] : null;
$opmerking = (isset($_POST['opmerking']) && trim($_POST['opmerking']) !== '') ? trim($_POST['opmerking']) : null;

// Als er iets is ingevuld, moet het wel een geldig getal zijn
if (($monteur_id !== null && !is_numeric($monteur_id)) || ($kilometerstand !== null && (!is_numeric($kilometerstand) || $kilometerstand < 0))) {
    stuurTerug($terug, 'ongeldig');
}

try {
    // Bestaat de afspraak?
    $stmt = $conn->prepare("SELECT afspraak_id FROM afspraken WHERE afspraak_id = :afspraak_id");
    $stmt->execute(['afspraak_id' => $afspraak_id]);
    if (!$stmt->fetch()) {
        include '404.php';
        exit;
    }

    // Is de gekozen monteur echt een monteur?
    if ($monteur_id !== null) {
        $stmt = $conn->prepare("SELECT users.user_id FROM users
                                JOIN rollen ON rollen.rol_id = users.rol_id
                                WHERE users.user_id = :monteur_id AND rollen.naam = 'monteur'");
        $stmt->execute(['monteur_id' => $monteur_id]);
        if (!$stmt->fetch()) {
            stuurTerug($terug, 'monteur');
        }
    }

    // UPDATE van de afspraak
    $stmt = $conn->prepare("UPDATE afspraken
                            SET monteur_id = :monteur_id, kilometerstand = :kilometerstand, opmerking = :opmerking
                            WHERE afspraak_id = :afspraak_id");
    $stmt->execute([
        'monteur_id' => $monteur_id,
        'kilometerstand' => $kilometerstand,
        'opmerking' => $opmerking,
        'afspraak_id' => $afspraak_id,
    ]);

    logWijziging($conn, 'Afspraak afgehandeld', 'afspraken',
        "Afspraak #$afspraak_id: monteur " . ($monteur_id ?? '-') . ", kilometerstand " . ($kilometerstand ?? '-'));
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

stuurTerug('beheer_afspraken_index.php', 'opgeslagen');
