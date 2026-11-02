<?php
require 'auth.php';
require 'meldingen.php';
vereisLogin();
vereisPost();
controleerCsrf();

$naam = trim($_POST['naam'] ?? '');
$email = trim($_POST['email'] ?? '');
$user_id = $_SESSION['user_id'];

// Alle velden moeten ingevuld zijn
if ($naam === '' || $email === '') {
    stuurTerug('profile_edit.php', 'velden');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    stuurTerug('profile_edit.php', 'ongeldig');
}

require 'database.php';

try {
    // Het e-mailadres mag niet al bij een ander account horen
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = :email AND user_id != :user_id");
    $stmt->execute(['email' => $email, 'user_id' => $user_id]);
    if ($stmt->fetch()) {
        stuurTerug('profile_edit.php', 'bestaat');
    }

    // UPDATE, en alleen van de ingelogde gebruiker zelf
    $stmt = $conn->prepare("UPDATE users
                            SET naam = :naam, email = :email
                            WHERE user_id = :user_id");
    $stmt->execute([
        'naam' => $naam,
        'email' => $email,
        'user_id' => $user_id,
    ]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    stuurTerug('profile_edit.php', 'fout');
}

// Sessie bijwerken, zodat de nieuwe naam meteen in de navigatie staat
$_SESSION['naam'] = $naam;
$_SESSION['email'] = $email;

stuurTerug('profile.php', 'opgeslagen');
