<?php
require 'auth.php';
require 'database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    include '404.php';
    exit;
}

$id = $_GET['id'];

try {
    $stmt = $conn->prepare("SELECT * FROM diensten WHERE dienst_id = :id AND deleted_at IS NULL");
    $stmt->execute(['id' => $id]);
    $dienst = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log de error (niet tonen aan gebruiker!)
    error_log("Database error: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

if (!$dienst) {
    include '404.php';
    exit;
}

$titel = $dienst['naam'];
require 'header.php';
?>

<main class="container">
    <a href="index.php#diensten" class="terug-link">&larr; Alle diensten</a>

    <article class="paneel detail-dienst">
        <div class="detail-afbeelding">
            <img src="<?php echo htmlspecialchars($dienst['images'] ?? 'images/Kleine-beurt.jpg') ?>" alt="<?php echo htmlspecialchars($dienst['naam']) ?>">
        </div>
        <div class="detail-tekst">
            <h1><?php echo htmlspecialchars($dienst['naam']) ?></h1>
            <p><?php echo htmlspecialchars($dienst['omschrijving']) ?></p>

            <dl class="kenmerken">
                <div>
                    <dt>Duur</dt>
                    <dd><?php echo htmlspecialchars($dienst['duur_minuten']) ?> minuten</dd>
                </div>
                <div>
                    <dt>Prijs</dt>
                    <dd class="prijs">&euro; <?php echo htmlspecialchars($dienst['prijs']) ?></dd>
                </div>
            </dl>

            <a href="afspraken_date.php?dienst_id=<?php echo htmlspecialchars($dienst['dienst_id']) ?>" class="knop knop-groot">Afspraak maken</a>
        </div>
    </article>
</main>

<?php require 'footer.php' ?>
