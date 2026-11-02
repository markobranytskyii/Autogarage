<?php
require 'auth.php';
require 'meldingen.php';
vereisRol('klant');
vereisPost();
controleerCsrf();

// Alle velden moeten ingevuld zijn
if (empty($_POST['kenteken']) || empty($_POST['merk']) || empty($_POST['model']) || empty($_POST['bouwjaar'])) {
    stuurTerug('autos_toevoegen.php', 'velden');
}

if (!is_numeric($_POST['bouwjaar'])) {
    stuurTerug('autos_toevoegen.php', 'ongeldig');
}

require 'database.php';

$kenteken = strtoupper(trim($_POST['kenteken']));
$merk = trim($_POST['merk']);
$model = trim($_POST['model']);
$bouwjaar = $_POST['bouwjaar'];
$user_id = $_SESSION['user_id'];

try {
    // Kenteken moet uniek zijn
    $stmt = $conn->prepare("SELECT auto_id FROM autos WHERE kenteken = :kenteken");
    $stmt->execute(['kenteken' => $kenteken]);
    if ($stmt->fetch()) {
        stuurTerug('autos_toevoegen.php', 'kenteken');
    }

    $stmt = $conn->prepare("INSERT INTO autos (user_id, kenteken, merk, model, bouwjaar) VALUES (:user_id, :kenteken, :merk, :model, :bouwjaar)");
    $stmt->execute(['user_id' => $user_id, 'kenteken' => $kenteken, 'merk' => $merk, 'model' => $model, 'bouwjaar' => $bouwjaar]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    stuurTerug('autos_toevoegen.php', 'fout');
}

stuurTerug('autos_index.php', 'toegevoegd');
