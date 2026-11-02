<?php
require 'auth.php';
require 'meldingen.php';

vereisPost();
controleerCsrf();

$naam = trim($_POST['naam'] ?? '');
$email = trim($_POST['email'] ?? '');
$wachtwoord = $_POST['wachtwoord'] ?? '';

// Alle velden moeten ingevuld zijn
if ($naam === '' || $email === '' || $wachtwoord === '') {
    stuurTerug('registreren.php', 'velden');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    stuurTerug('registreren.php', 'ongeldig');
}

if (strlen($wachtwoord) < 8) {
    stuurTerug('registreren.php', 'wachtwoord');
}

require 'database.php';

try {
    // Bestaat dit e-mailadres al?
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        stuurTerug('registreren.php', 'bestaat');
    }

    $rol_id = 1; // 1 = klant
    $stmt = $conn->prepare("INSERT INTO users (email, wachtwoord, naam, rol_id) VALUES (:email, :wachtwoord, :naam, :rol_id)");
    $stmt->execute([
        'email' => $email,
        'wachtwoord' => password_hash($wachtwoord, PASSWORD_DEFAULT),
        'naam' => $naam,
        'rol_id' => $rol_id,
    ]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    stuurTerug('registreren.php', 'fout');
}

header("Location: login.php?melding=geregistreerd");
exit;
