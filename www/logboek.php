<?php
require 'auth.php';
vereisRol('admin');
require 'database.php';

try {
    // Alle wijzigingen ophalen, met de naam van de gebruiker die de wijziging deed (nieuwste eerst)
    $stmt = $conn->prepare("SELECT wijzigingen.*, users.naam AS gebruikersnaam
                            FROM wijzigingen
                            JOIN users ON users.user_id = wijzigingen.user_id
                            ORDER BY wijzigingen.created_at DESC");
    $stmt->execute();
    $wijzigingen = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log de error (niet tonen aan gebruiker!)
    error_log("Database error: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

$titel = 'Logboek';
include 'header.php';
?>

<main class="container">
    <div class="pagina-kop">
        <div>
            <h1>Logboek</h1>
            <p>Welke medewerker heeft wat aangepast, nieuwste eerst.</p>
        </div>
        <span class="teller"><?php echo count($wijzigingen) ?> regels</span>
    </div>

    <div class="paneel">
        <?php if (count($wijzigingen) == 0): ?>
            <p class="leeg">Er zijn nog geen wijzigingen.</p>
        <?php else: ?>
            <div class="tabel-wrap">
                <table class="tabel">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Gebruiker</th>
                            <th>Actie</th>
                            <th>Tabel</th>
                            <th>Omschrijving</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($wijzigingen as $wijzig): ?>
                            <tr>
                                <!-- htmlspecialchars op elk veld: XSS voorkomen -->
                                <td class="nowrap"><?php echo htmlspecialchars(date('d-m-Y H:i', strtotime($wijzig['created_at']))) ?></td>
                                <td><?php echo htmlspecialchars($wijzig['gebruikersnaam']) ?></td>
                                <td><span class="status"><?php echo htmlspecialchars($wijzig['actie']) ?></span></td>
                                <td><code><?php echo htmlspecialchars($wijzig['tabel_naam']) ?></code></td>
                                <td><?php echo htmlspecialchars($wijzig['omschrijving'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>
