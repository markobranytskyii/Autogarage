<?php
require 'auth.php';
require 'meldingen.php';

vereisPost();
controleerCsrf();

$email    = trim($_POST['email'] ?? '');
$password = $_POST['wachtwoord'] ?? '';

if ($email === '' || $password === '') {
    stuurTerug('login.php', 'velden');
}

require 'database.php';

$stmt = $conn->prepare("SELECT users.*, rollen.naam AS rol
                        FROM users
                        JOIN rollen ON rollen.rol_id = users.rol_id
                        WHERE users.email = :email AND users.deleted_at IS NULL");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (is_array($user) && password_verify($password, $user['wachtwoord'])) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['email']   = $user['email'];
    $_SESSION['role']    = $user['rol'];   // 'klant', 'medewerker', 'monteur' of 'admin'
    $_SESSION['naam']    = $user['naam'];

    if ($user['rol'] === 'admin') {
        header('Location: statistieken.php');
        exit;
    }

    if ($user['rol'] === 'medewerker') {
        header('Location: beheer_afspraken_index.php');
        exit;
    }

    header('Location: profile.php');
    exit;
}

stuurTerug('login.php', 'inloggegevens');
