<?php
// Eenmalig hulpscript: zet wachtwoorden die nog als platte tekst in de database staan om naar een hash.
// Staat bewust buiten de map www/, zodat het niet via de website te openen is.
// Uitvoeren (vanuit de hoofdmap van het project):
//   docker compose run --rm -v ./scripts:/scripts web php /scripts/hashing.php
// Leest de databasegegevens uit dezelfde .env-variabelen als de website.

$databaseBestand = file_exists(__DIR__ . '/../www/database.php')
    ? __DIR__ . '/../www/database.php'
    : '/var/www/html/database.php';
require $databaseBestand;

$users = $conn->query("SELECT email, wachtwoord FROM users")->fetchAll(PDO::FETCH_ASSOC);
$stmt = $conn->prepare("UPDATE users SET wachtwoord = :password WHERE email = :email");

$aantal = 0;
foreach ($users as $user) {
    $passwordInfo = password_get_info($user['wachtwoord']);

    if ($passwordInfo['algo'] === null) {
        $stmt->execute([
            'password' => password_hash($user['wachtwoord'], PASSWORD_DEFAULT),
            'email' => $user['email'],
        ]);
        $aantal++;
    }
}

echo "$aantal wachtwoord(en) gehasht." . PHP_EOL;
