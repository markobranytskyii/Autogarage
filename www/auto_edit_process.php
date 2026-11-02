<?php
require 'auth.php';
require 'meldingen.php';
vereisRol('klant');
vereisPost();
controleerCsrf();

if (empty($_POST['auto_id']) || !is_numeric($_POST['auto_id'])) {
    stuurTerug('autos_index.php', 'ongeldig');
}

$auto_id = $_POST['auto_id'];
$terug = 'auto_edit.php?id=' . $auto_id;

// Alle velden moeten ingevuld zijn
if (empty($_POST['kenteken']) || empty($_POST['merk']) || empty($_POST['model']) || empty($_POST['bouwjaar'])) {
    stuurTerug($terug, 'velden');
}

if (!is_numeric($_POST['bouwjaar'])) {
    stuurTerug($terug, 'ongeldig');
}

require 'database.php';

$kenteken = strtoupper(trim($_POST['kenteken']));
$merk = trim($_POST['merk']);
$model = trim($_POST['model']);
$bouwjaar = $_POST['bouwjaar'];
$user_id = $_SESSION['user_id'];

try {
    // Kenteken mag niet al bij een andere auto horen
    $stmt = $conn->prepare("SELECT auto_id FROM autos WHERE kenteken = :kenteken AND auto_id != :auto_id");
    $stmt->execute(['kenteken' => $kenteken, 'auto_id' => $auto_id]);
    if ($stmt->fetch()) {
        stuurTerug($terug, 'kenteken');
    }

    // UPDATE, en alleen als de auto van de ingelogde gebruiker is
    $stmt = $conn->prepare("UPDATE autos
                            SET kenteken = :kenteken, merk = :merk, model = :model, bouwjaar = :bouwjaar
                            WHERE auto_id = :auto_id AND user_id = :user_id");
    $stmt->execute([
        'kenteken' => $kenteken,
        'merk' => $merk,
        'model' => $model,
        'bouwjaar' => $bouwjaar,
        'auto_id' => $auto_id,
        'user_id' => $user_id,
    ]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    stuurTerug($terug, 'fout');
}

stuurTerug('autos_index.php', 'opgeslagen');
