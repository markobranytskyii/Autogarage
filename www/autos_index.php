<?php
require 'auth.php';
require 'meldingen.php';
vereisRol('klant');
require 'database.php';

$user_id = $_SESSION['user_id'];

try {
    // Alle auto's van de ingelogde gebruiker ophalen (verwijderde auto's niet tonen).
    $stmt = $conn->prepare("SELECT *
                            FROM autos
                            WHERE user_id = :user_id
                            AND deleted_at IS NULL
                            ORDER BY merk, model");
    $stmt->execute(['user_id' => $user_id]);
    $autos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

$titel = "Mijn auto's";
require 'header.php';
?>

<main class="container">
    <div class="pagina-kop">
        <div>
            <h1>Mijn auto's</h1>
            <p>Voeg je auto's toe, zodat je er een afspraak voor kunt maken.</p>
        </div>
        <a href="autos_toevoegen.php" class="knop">+ Auto toevoegen</a>
    </div>

    <?php toonMelding(); ?>

    <div class="paneel">
        <?php if (count($autos) == 0): ?>
            <p class="leeg">Je hebt nog geen auto's toegevoegd.</p>
        <?php else: ?>
            <div class="tabel-wrap">
                <table class="tabel">
                    <thead>
                        <tr>
                            <th>Kenteken</th>
                            <th>Merk</th>
                            <th>Model</th>
                            <th>Bouwjaar</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($autos as $auto): ?>
                            <tr>
                                <!-- htmlspecialchars op elk veld: XSS voorkomen -->
                                <td><span class="kenteken"><?php echo htmlspecialchars($auto['kenteken']) ?></span></td>
                                <td><?php echo htmlspecialchars($auto['merk']) ?></td>
                                <td><?php echo htmlspecialchars($auto['model']) ?></td>
                                <td><?php echo htmlspecialchars($auto['bouwjaar']) ?></td>
                                <td class="rechts">
                                    <a href="auto_edit.php?id=<?php echo htmlspecialchars($auto['auto_id']) ?>" class="tabel-link">Bewerken</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require 'footer.php' ?>
