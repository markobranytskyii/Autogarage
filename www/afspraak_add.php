<?php
require 'auth.php';
require 'database.php';

header('Content-Type: application/json'); // vertelt de browser dat we JSON teruggeven

// Lees de JSON body
$input = file_get_contents('php://input'); // leest de JSON body
$data = json_decode($input, true);          // decode de JSON body

// Ingelogd, geldig CSRF-token en zijn alle velden aanwezig?
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!isIngelogd()
    || $_SERVER['REQUEST_METHOD'] !== 'POST'
    || !hash_equals(csrfToken(), $token)
    || !isset($data['auto_id'], $data['dienst_id'], $data['datum'], $data['tijd'])) {
    echo json_encode(['success' => false]);
    exit;
}

// Datum moet een geldige datum zijn en de tijd een van de vaste tijdsloten
$geldigeDatum = DateTime::createFromFormat('Y-m-d', $data['datum']);
if (!$geldigeDatum || !in_array($data['tijd'], ['10:00', '13:00', '16:00'], true)) {
    echo json_encode(['success' => false]);
    exit;
}

$user_id   = $_SESSION['user_id'];
$auto_id   = $data['auto_id'];
$dienst_id = $data['dienst_id'];
$datum     = $data['datum'];
$tijd      = $data['tijd'];

try {
    // Controleer dat de auto echt van de ingelogde gebruiker is (veiligheid)
    $stmt = $conn->prepare("SELECT auto_id FROM autos WHERE auto_id = :auto_id AND user_id = :user_id AND deleted_at IS NULL");
    $stmt->execute(['auto_id' => $auto_id, 'user_id' => $user_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false]);
        exit;
    }

    // Prijs op de SERVER ophalen (niet vertrouwen op de browser)
    $stmt = $conn->prepare("SELECT prijs FROM diensten WHERE dienst_id = :dienst_id AND deleted_at IS NULL");
    $stmt->execute(['dienst_id' => $dienst_id]);
    $dienst = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$dienst) {
        echo json_encode(['success' => false]);
        exit;
    }
    $prijs = $dienst['prijs'];

    // Afspraak opslaan met de op de server opgehaalde prijs (monteur_id blijft nog leeg)
    $stmt = $conn->prepare("INSERT INTO afspraken (auto_id, dienst_id, datum, tijd, prijs)
                            VALUES (:auto_id, :dienst_id, :datum, :tijd, :prijs)");
    $stmt->execute([
        'auto_id'   => $auto_id,
        'dienst_id' => $dienst_id,
        'datum'     => $datum,
        'tijd'      => $tijd,
        'prijs'     => $prijs,
    ]);

    // Teller bijwerken in de sessie (aantal afspraken van deze gebruiker)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM afspraken
                            JOIN autos ON autos.auto_id = afspraken.auto_id
                            WHERE autos.user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $_SESSION['afspraak_count'] = (int) $stmt->fetchColumn();

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false]);
    exit;
}

echo json_encode(['success' => true]);
