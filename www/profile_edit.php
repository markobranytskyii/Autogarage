<?php
require 'auth.php';
require 'meldingen.php';
vereisLogin();
require 'database.php';

$user_id = $_SESSION['user_id'];

try {
    // De gegevens van de ingelogde gebruiker ophalen
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

// Bestaat de gebruiker niet, dan niet tonen
if (!$user) {
    include '404.php';
    exit;
}

$titel = 'Profiel wijzigen';
require 'header.php';
?>

<main class="container">
    <a href="profile.php" class="terug-link">&larr; Terug naar profiel</a>

    <div class="paneel paneel-smal">
        <h1>Profiel wijzigen</h1>

        <?php toonMelding(); ?>

        <form action="profile_edit_process.php" method="post" class="formulier">
            <?php echo csrfVeld() ?>
            <div class="veld">
                <label for="naam">Naam</label>
                <input type="text" name="naam" id="naam" value="<?= htmlspecialchars($user['naam']) ?>" required>
            </div>
            <div class="veld">
                <label for="email">E-mailadres</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <button type="submit" name="submit" class="knop knop-breed">Opslaan</button>
        </form>
    </div>
</main>

<?php include 'footer.php'; ?>
