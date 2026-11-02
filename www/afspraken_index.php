<?php
require 'auth.php';
vereisRol('klant');
require 'database.php';

$user_id = $_SESSION['user_id'];

try {
    // Afspraken van de gebruiker, met kenteken (autos) en dienstnaam (diensten) via LEFT JOIN
    $stmt = $conn->prepare("SELECT afspraken.datum, afspraken.tijd, afspraken.prijs,
                                   autos.kenteken, diensten.naam AS dienst
                            FROM afspraken
                            LEFT JOIN autos ON autos.auto_id = afspraken.auto_id
                            LEFT JOIN diensten ON diensten.dienst_id = afspraken.dienst_id
                            WHERE autos.user_id = :user_id
                            ORDER BY afspraken.datum, afspraken.tijd");
    $stmt->execute(['user_id' => $user_id]);
    $afspraken = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

$titel = 'Mijn afspraken';
require 'header.php';
?>

<main class="container">
    <div class="pagina-kop">
        <div>
            <h1>Mijn afspraken</h1>
            <p>Een overzicht van al je geplande en afgeronde afspraken.</p>
        </div>
        <a href="index.php#diensten" class="knop">+ Nieuwe afspraak</a>
    </div>

    <div class="paneel">
        <?php if (count($afspraken) == 0): ?>
            <p class="leeg">Je hebt nog geen afspraken.</p>
        <?php else: ?>
            <div class="tabel-wrap">
                <table class="tabel">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Tijd</th>
                            <th>Kenteken</th>
                            <th>Dienst</th>
                            <th class="rechts">Prijs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($afspraken as $afspraak): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($afspraak['datum']))) ?></td>
                                <td><?php echo htmlspecialchars(substr($afspraak['tijd'], 0, 5)) ?></td>
                                <td><span class="kenteken"><?php echo htmlspecialchars($afspraak['kenteken']) ?></span></td>
                                <td><?php echo htmlspecialchars($afspraak['dienst'] ?? '') ?></td>
                                <td class="rechts">&euro; <?php echo htmlspecialchars($afspraak['prijs']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require 'footer.php' ?>
