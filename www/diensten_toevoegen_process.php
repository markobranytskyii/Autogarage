<?php
require 'auth.php';
require 'meldingen.php';
vereisRol('admin', 'medewerker');
vereisPost();
controleerCsrf();

// Alle velden moeten ingevuld zijn (prijs mag 0 zijn, daarom geen empty())
if (empty($_POST['naam']) || empty($_POST['omschrijving']) || empty($_POST['duur_minuten']) || !isset($_POST['prijs']) || $_POST['prijs'] === '') {
    stuurTerug('diensten_toevoegen.php', 'velden');
}

if (!is_numeric($_POST['duur_minuten']) || !is_numeric($_POST['prijs']) || $_POST['prijs'] < 0) {
    stuurTerug('diensten_toevoegen.php', 'ongeldig');
}

require 'database.php';

$naam = trim($_POST['naam']);
$omschrijving = trim($_POST['omschrijving']);
$duur_minuten = $_POST['duur_minuten'];
$prijs = $_POST['prijs'];
// afbeelding is optioneel: leeg = NULL in de database
$images = !empty($_POST['images']) ? $_POST['images'] : null;

try {
    $stmt = $conn->prepare("INSERT INTO diensten (naam, omschrijving, duur_minuten, prijs, images) VALUES (:naam, :omschrijving, :duur_minuten, :prijs, :images)");
    $stmt->execute(['naam' => $naam, 'omschrijving' => $omschrijving, 'duur_minuten' => $duur_minuten, 'prijs' => $prijs, 'images' => $images]);

    logWijziging($conn, 'Dienst toegevoegd', 'diensten', "Nieuwe dienst \"$naam\" voor € $prijs");
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    stuurTerug('diensten_toevoegen.php', 'fout');
}

stuurTerug('diensten_index.php', 'toegevoegd');
