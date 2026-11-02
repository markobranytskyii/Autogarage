<?php

// Databaseverbinding. De gegevens komen uit .env, niet uit de code.
// Met Docker zet docker-compose ze als omgevingsvariabelen klaar.
// Op gewone webhosting (zonder Docker) lezen we ze uit het bestand www/.env.
if (!getenv('MYSQL_DATABASE') && file_exists(__DIR__ . '/.env')) {
    foreach (parse_ini_file(__DIR__ . '/.env', false, INI_SCANNER_RAW) as $naam => $waarde) {
        putenv("$naam=$waarde");
    }
}

$dbhost = getenv('MYSQL_HOST') ?: 'mariadb';
$dbname = getenv('MYSQL_DATABASE');
$dbuser = getenv('MYSQL_USER');
$dbpass = getenv('MYSQL_PASSWORD');

try {
    $conn = new PDO("mysql:host=$dbhost;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Log de error (niet tonen aan gebruiker!)
    error_log("Database error: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

// Schrijft een regel in het logboek (tabel wijzigingen), zodat de admin ziet wie wat heeft aangepast.
function logWijziging(PDO $conn, string $actie, string $tabel, string $omschrijving): void
{
    $stmt = $conn->prepare("INSERT INTO wijzigingen (user_id, actie, tabel_naam, omschrijving)
                            VALUES (:user_id, :actie, :tabel_naam, :omschrijving)");
    $stmt->execute([
        'user_id' => $_SESSION['user_id'],
        'actie' => $actie,
        'tabel_naam' => $tabel,
        'omschrijving' => $omschrijving,
    ]);
}
