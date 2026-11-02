<?php
require 'auth.php';
vereisRol('admin', 'medewerker');
require 'database.php';

try {
    // Auto's die nog nooit een afspraak hebben gehad, met de naam van de eigenaar (LEFT JOIN + IS NULL).
    $stmt = $conn->prepare("SELECT autos.*, users.naam AS eigenaar
                            FROM autos
                            JOIN users ON users.user_id = autos.user_id
                            LEFT JOIN afspraken ON afspraken.auto_id = autos.auto_id
                            WHERE autos.deleted_at IS NULL
                            AND afspraken.afspraak_id IS NULL
                            ORDER BY autos.merk, autos.model");
    $stmt->execute();
    $autos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

$titel = "Auto's zonder afspraak";
require 'header.php';
?>

<main class="container">
    <div class="pagina-kop">
        <div>
            <h1>Auto's zonder afspraak</h1>
            <p>Deze auto's zijn nog nooit bij ons binnen geweest. Een goede reden om de eigenaar te benaderen.</p>
        </div>
        <span class="teller"><?php echo count($autos) ?> auto's</span>
    </div>

    <div class="paneel">
        <?php if (count($autos) == 0): ?>
            <p class="leeg">Alle auto's hebben minstens één afspraak gehad.</p>
        <?php else: ?>
            <div class="tabel-wrap">
                <table class="tabel">
                    <thead>
                        <tr>
                            <th>Kenteken</th>
                            <th>Merk</th>
                            <th>Model</th>
                            <th>Bouwjaar</th>
                            <th>Eigenaar</th>
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
                                <td><?php echo htmlspecialchars($auto['eigenaar']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require 'footer.php' ?>
