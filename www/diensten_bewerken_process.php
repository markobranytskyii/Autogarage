<?php
require 'auth.php';
require 'meldingen.php';
vereisRol('admin', 'medewerker');
vereisPost();
controleerCsrf();

if (empty($_POST['dienst_id']) || !is_numeric($_POST['dienst_id'])) {
    stuurTerug('diensten_index.php', 'ongeldig');
}

$dienst_id = $_POST['dienst_id'];
$terug = 'diensten_bewerken.php?id=' . $dienst_id;

// Alle velden moeten ingevuld zijn (prijs mag 0 zijn)
if (empty($_POST['naam']) || empty($_POST['omschrijving']) || empty($_POST['duur_minuten']) || !isset($_POST['prijs']) || $_POST['prijs'] === '') {
    stuurTerug($terug, 'velden');
}

if (!is_numeric($_POST['duur_minuten']) || !is_numeric($_POST['prijs']) || $_POST['prijs'] < 0) {
    stuurTerug($terug, 'ongeldig');
}

require 'database.php';

$naam = trim($_POST['naam']);
$omschrijving = trim($_POST['omschrijving']);
$duur_minuten = $_POST['duur_minuten'];
$prijs = $_POST['prijs'];

try {
    // UPDATE van de dienst
    $stmt = $conn->prepare("UPDATE diensten
                            SET naam = :naam, omschrijving = :omschrijving, duur_minuten = :duur_minuten, prijs = :prijs
                            WHERE dienst_id = :dienst_id");
    $stmt->execute([
        'naam' => $naam,
        'omschrijving' => $omschrijving,
        'duur_minuten' => $duur_minuten,
        'prijs' => $prijs,
        'dienst_id' => $dienst_id,
    ]);

    logWijziging($conn, 'Dienst bewerkt', 'diensten', "Dienst \"$naam\": $duur_minuten minuten, € $prijs");
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    stuurTerug($terug, 'fout');
}

stuurTerug('diensten_index.php', 'opgeslagen');
