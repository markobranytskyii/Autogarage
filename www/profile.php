<?php
require 'auth.php';
require 'meldingen.php';
vereisLogin();
require 'database.php';

$user_id = $_SESSION['user_id'];

try {
    // Join met rollen om de naam van de rol te tonen in plaats van alleen het nummer.
    $stmt = $conn->prepare("SELECT users.*, rollen.naam AS rol
                            FROM users
                            JOIN rollen ON rollen.rol_id = users.rol_id
                            WHERE users.user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

if (!$user) {
    include '404.php';
    exit;
}

$titel = 'Mijn profiel';
require 'header.php';
?>

<main class="container">
    <div class="paneel paneel-smal">
        <div class="profiel-kop">
            <span class="avatar"><?php echo htmlspecialchars(mb_strtoupper(mb_substr($user['naam'], 0, 1))) ?></span>
            <div>
                <h1><?php echo htmlspecialchars($user['naam']) ?></h1>
                <span class="rol-label"><?php echo htmlspecialchars($user['rol']) ?></span>
            </div>
        </div>

        <?php toonMelding(); ?>

        <!-- htmlspecialchars op elk veld: XSS voorkomen -->
        <dl class="kenmerken kenmerken-lijst">
            <div><dt>E-mailadres</dt><dd><?php echo htmlspecialchars($user['email']) ?></dd></div>
            <div><dt>Rol</dt><dd><?php echo htmlspecialchars(ucfirst($user['rol'])) ?></dd></div>
            <div><dt>Lid sinds</dt><dd><?php echo htmlspecialchars(date('d-m-Y', strtotime($user['created_at']))) ?></dd></div>
        </dl>

        <div class="knoppen-rij">
            <a href="profile_edit.php" class="knop">Gegevens wijzigen</a>
            <a href="index.php" class="knop knop-secundair">Naar home</a>
        </div>
    </div>
</main>

<?php require 'footer.php' ?>
