<?php
// Aantal afspraken van de ingelogde klant (voor de teller in de navbar).
$afspraak_count = $_SESSION['afspraak_count'] ?? 0;
if (isset($_SESSION['user_id']) && isset($conn)) {
    try {
        $telStmt = $conn->prepare("SELECT COUNT(*) FROM afspraken
                                   JOIN autos ON autos.auto_id = afspraken.auto_id
                                   WHERE autos.user_id = :user_id");
        $telStmt->execute(['user_id' => $_SESSION['user_id']]);
        $afspraak_count = (int) $telStmt->fetchColumn();
        $_SESSION['afspraak_count'] = $afspraak_count;
    } catch (PDOException $e) {
        // stil: teller blijft op de sessiewaarde staan
    }
}

$huidigePagina = basename($_SERVER['PHP_SELF']);
$rol = $_SESSION['role'] ?? '';

// Hulpfunctie: link met class "actief" als het de huidige pagina is.
function navLink(string $href, string $tekst, string $huidigePagina, string $extra = ''): string
{
    $actief = $href === $huidigePagina ? ' class="actief"' : '';
    return '<a href="' . $href . '"' . $actief . '>' . $tekst . $extra . '</a>';
}
?>
<nav class="navbar">
    <?php echo navLink('index.php', 'Home', $huidigePagina) ?>

    <?php if (isset($_SESSION['user_id'])) : ?>

        <?php if ($rol === 'klant') : ?>
            <?php echo navLink('afspraken_index.php', 'Mijn afspraken', $huidigePagina, ' <span class="afspraak-count">' . (int) $afspraak_count . '</span>') ?>
            <?php echo navLink('autos_index.php', "Mijn auto's", $huidigePagina) ?>
        <?php endif; ?>

        <?php if ($rol === 'medewerker' || $rol === 'admin') : ?>
            <?php echo navLink('beheer_afspraken_index.php', 'Afspraken', $huidigePagina) ?>
            <?php echo navLink('diensten_index.php', 'Diensten', $huidigePagina) ?>
            <?php echo navLink('autos_zonder_afspraak.php', "Auto's zonder afspraak", $huidigePagina) ?>
            <?php echo navLink('diensten_softdelete_index.php', 'Prullenbak', $huidigePagina) ?>
        <?php endif; ?>

        <?php if ($rol === 'admin') : ?>
            <?php echo navLink('statistieken.php', 'Statistieken', $huidigePagina) ?>
            <?php echo navLink('logboek.php', 'Logboek', $huidigePagina) ?>
        <?php endif; ?>

        <span class="nav-gebruiker">
            <?php echo navLink('profile.php', htmlspecialchars($_SESSION['naam'] ?? 'Profiel'), $huidigePagina, ' <span class="rol-label">' . htmlspecialchars($rol) . '</span>') ?>
            <a href="logout.php" class="nav-uitloggen">Uitloggen</a>
        </span>

    <?php else : ?>
        <span class="nav-gebruiker">
            <?php echo navLink('login.php', 'Inloggen', $huidigePagina) ?>
            <a href="registreren.php" class="knop knop-klein">Registreren</a>
        </span>
    <?php endif; ?>
</nav>
